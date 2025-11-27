<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PostulanteController extends Controller
{
	/**
	 * Mostrar formulario completo para insertar postulante
	 */
	public function create()
	{
		return view('talent.insertar_postulante');
	}

	/**
	 * Consulta datos por DNI (usa API pública como fallback)
	 */
	public function consulta(Request $request)
	{
		$request->validate(['dni' => 'required|string']);
		$dni = $request->input('dni');

		try {
			// Intento: usar API pública como fallback
			$resp = Http::asForm()->post('https://buscardniperu.com/wp-admin/admin-ajax.php', [
				'dni' => $dni,
				'action' => 'consulta_dni_api',
				'tipo' => 'dni',
				'pagina' => '1',
			]);

			if ($resp->successful()) {
				$data = $resp->json();
				$filtered = $this->filterDNIData($data);
				return response()->json(['success' => true, 'data' => $filtered, 'source' => 'public_api']);
			}

			Log::error('Consulta DNI pública falló: HTTP ' . $resp->status());
			return response()->json(['success' => false, 'error' => 'API no disponible'], 503);
		} catch (\Exception $e) {
			Log::error('Error consulta DNI: ' . $e->getMessage());
			return response()->json(['success' => false, 'error' => 'Error interno'], 500);
		}
	}

	/**
	 * Verifica si un DNI o Carnet de Extranjería ya existe en la tabla de postulantes
	 */
	public function checkDNI(Request $request)
	{
		$request->validate(['dni' => 'required|string']);
		$dni = $request->input('dni');

		// Verificar si existe en postulantes activos (no cancelados)
		$exists = DB::table('raz_postulantes')
			->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
			->where('raz_postulantes.dni', $dni)
			->where('raz_convocatorias.estado', '!=', 'Cancelada')
			->exists();

		return response()->json(['success' => true, 'exists' => $exists]);
	}

	/**
	 * Obtener postulantes asociados a una convocatoria
	 */
	public function byConvocatoria($convocatoriaId)
	{
		try {
			$postulantes = DB::table('raz_postulantes')
				->where('convocatoria_id', $convocatoriaId)
				->select('id', 'dni', 'tipo_documento', 'nombres', 'ap_pat', 'ap_mat', 'celular', 'correo', 'created_at')
				->orderBy('created_at', 'desc')
				->get();

			// Verificar si algún postulante ya fue capacitado
			$yaCapacitados = false;
			if ($postulantes->isNotEmpty()) {
				$dnis = $postulantes->pluck('dni')->toArray();
				$yaCapacitados = DB::table('Postulantes_En_Formacion')
					->whereIn('DNI', $dnis)
					->exists();
			}

			return response()->json([
				'success' => true,
				'data' => $postulantes,
				'ya_capacitados' => $yaCapacitados
			]);
		} catch (\Exception $e) {
			Log::error('Error obteniendo postulantes por convocatoria: ' . $e->getMessage());
			return response()->json(['success' => false, 'error' => 'No se pudo obtener postulantes'], 500);
		}
	}

	/**
	 * Guardar postulante desde modal (acepta campos mínimos y asigna defaults)
	 */
	public function storeFromModal(Request $request)
	{
		try {
			$validated = $request->validate([
				'tipo_documento' => 'required|string',
				'dni' => [
					'required',
					'string',
					function ($attribute, $value, $fail) {
						$exists = DB::table('raz_postulantes')
							->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
							->where('raz_postulantes.dni', $value)
							->where('raz_convocatorias.estado', '!=', 'Cancelada')
							->exists();

						if ($exists) {
							$fail('El DNI ya está registrado en una convocatoria activa.');
						}
					}
				],
				'nombres' => 'required|string',
				'ap_pat' => 'required|string',
				'ap_mat' => 'required|string',
				'celular' => 'required|string',
				'whatsapp' => 'required|string',
				'correo' => 'required|email',
				'convocatoria_id' => 'nullable|integer',
			]);

			$data = [
				'tipo_documento' => $request->input('tipo_documento', 'DNI'),
				'dni' => $request->input('dni'),
				'nombres' => $request->input('nombres'),
				'ap_pat' => $request->input('ap_pat'),
				'ap_mat' => $request->input('ap_mat'),
				'fecha_nac' => $request->input('fecha_nac') ?: date('Y-m-d'),
				'direccion' => $request->input('direccion') ?: '',
				'est_civil' => $request->input('est_civil') ?: null,
				'sexo' => $request->input('sexo') ?: '0',
				'nacionalidad' => $request->input('nacionalidad') ?: null,
				'celular' => $request->input('celular'),
				'whatsapp' => $request->input('whatsapp'),
				'correo' => $request->input('correo'),
				'provincia' => $request->input('provincia') ?: null,
				'distrito' => $request->input('distrito') ?: null,
				'experiencia_callcenter' => $request->input('experiencia_callcenter') ?: 'no',
				'discapacidad' => $request->input('discapacidad') ?: 'no',
				'tipo_discapacidad' => $request->input('tipo_discapacidad') ?: null,
				'tipo_contrato' => $request->input('tipo_contrato') ?: null,
				'modalidad_trabajo' => $request->input('modalidad_trabajo') ?: 'presencial',
				'tipo_gestion' => $request->input('tipo_gestion') ?: '0',
				'convocatoria_id' => $request->input('convocatoria_id') ?: null,
			];

			// Formatear fecha_nac
			if (!empty($data['fecha_nac'])) {
				$fecha = $data['fecha_nac'];
				if (strlen($fecha) > 10)
					$fecha = substr($fecha, 0, 10);
				$data['fecha_nac'] = (string) Carbon::createFromFormat('Y-m-d', $fecha)->toDateString();
			}

			$columns = array_keys($data);
			$placeholders = [];
			$bindings = [];
			foreach ($columns as $col) {
				if ($col === 'fecha_nac') {
					$placeholders[] = "CONVERT(date, ?, 23)";
					$bindings[] = $data[$col];
				} else {
					$placeholders[] = "?";
					$bindings[] = $data[$col];
				}
			}

			$sql = 'INSERT INTO [raz_postulantes] (' . implode(', ', $columns) . ', created_at, updated_at) VALUES (' . implode(', ', $placeholders) . ', GETDATE(), GETDATE())';
			$inserted = DB::insert($sql, $bindings);

			if ($inserted)
				return response()->json(['success' => true]);
			return response()->json(['success' => false, 'error' => 'No se pudo insertar'], 500);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return response()->json(['success' => false, 'errors' => $e->errors()], 422);
		} catch (\Exception $e) {
			Log::error('Error storeFromModal: ' . $e->getMessage());
			return response()->json(['success' => false, 'error' => 'Error interno'], 500);
		}
	}

	/**
	 * Mostrar detalles de un postulante por id
	 */
	public function show($id)
	{
		try {
			$p = DB::table('raz_postulantes')->where('id', $id)->first();
			if (!$p)
				return response()->json(['success' => false, 'error' => 'No existe postulante'], 404);
			return response()->json(['success' => true, 'data' => $p]);
		} catch (\Exception $e) {
			Log::error('Error obteniendo detalles postulante: ' . $e->getMessage());
			return response()->json(['success' => false, 'error' => 'Error interno'], 500);
		}
	}

	/**
	 * Guardar postulante (formulario completo)
	 */
	public function store(Request $request)
	{
		try {
			$validated = $request->validate([
				'tipo_documento' => 'required|string',
				'dni' => [
					'required',
					'string',
					function ($attribute, $value, $fail) {
						$exists = DB::table('raz_postulantes')
							->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
							->where('raz_postulantes.dni', $value)
							->where('raz_convocatorias.estado', '!=', 'Cancelada')
							->exists();

						if ($exists) {
							$fail('El DNI ya está registrado en una convocatoria activa.');
						}
					}
				],
				'nombres' => 'required|string',
				'ap_pat' => 'required|string',
				'ap_mat' => 'required|string',
				'fecha_nac' => 'required|date',
				'direccion' => 'required|string',
				'est_civil' => 'nullable|string', // Permite valores nulos/vacíos
				'sexo' => 'required|string',
				'nacionalidad' => 'nullable|string',
				'celular' => 'required|string',
				'whatsapp' => 'required|string',
				'correo' => 'required|email',
				'provincia' => 'nullable|string',
				'distrito' => 'nullable|string',
				'experiencia_callcenter' => 'required|string',
				'discapacidad' => 'required|string',
				'tipo_discapacidad' => 'nullable|string',
				'tipo_contrato' => 'nullable|string',
				'modalidad_trabajo' => 'required|string',
				'tipo_gestion' => 'required|string',
				'convocatoria_id' => 'nullable|integer',
			]);

			$data = $request->only([
				'tipo_documento',
				'dni',
				'nombres',
				'ap_pat',
				'ap_mat',
				'fecha_nac',
				'direccion',
				'est_civil',
				'sexo',
				'nacionalidad',
				'celular',
				'whatsapp',
				'correo',
				'provincia',
				'distrito',
				'experiencia_callcenter',
				'discapacidad',
				'tipo_discapacidad',
				'tipo_contrato',
				'modalidad_trabajo',
				'tipo_gestion',
				'convocatoria_id'
			]);

			// Convertir campos vacíos a null para campos nullable
			$nullableFields = ['est_civil', 'nacionalidad', 'provincia', 'distrito', 'tipo_discapacidad', 'tipo_contrato', 'convocatoria_id'];
			foreach ($nullableFields as $field) {
				if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
					$data[$field] = null;
				}
			}

			if (isset($data['fecha_nac']) && !empty($data['fecha_nac'])) {
				$fecha = $data['fecha_nac'];
				if (strlen($fecha) > 10)
					$fecha = substr($fecha, 0, 10);
				$data['fecha_nac'] = (string) Carbon::createFromFormat('Y-m-d', $fecha)->toDateString();
			}

			$columns = array_keys($data);
			$placeholders = [];
			$bindings = [];
			foreach ($columns as $col) {
				if ($col === 'fecha_nac') {
					$placeholders[] = "CONVERT(date, ?, 23)";
					$bindings[] = $data[$col];
				} else {
					$placeholders[] = "?";
					$bindings[] = $data[$col];
				}
			}

			$sql = 'INSERT INTO [raz_postulantes] (' . implode(', ', $columns) . ', created_at, updated_at) VALUES (' . implode(', ', $placeholders) . ', GETDATE(), GETDATE())';
			$inserted = DB::insert($sql, $bindings);

			if ($inserted) {
				return response()->json(['success' => true, 'message' => 'Postulante guardado exitosamente.']);
			}

			return response()->json(['success' => false, 'error' => 'No se pudo insertar postulante'], 500);
		} catch (\Illuminate\Validation\ValidationException $e) {
			Log::error('Errores de validación: ' . json_encode($e->errors()));
			return response()->json(['success' => false, 'error' => 'Errores de validación', 'errors' => $e->errors()], 422);
		} catch (\Exception $e) {
			Log::error('Error guardando postulante: ' . $e->getMessage() . ' - Stack: ' . $e->getTraceAsString());
			return response()->json(['success' => false, 'error' => 'No se pudo guardar postulante'], 500);
		}
	}

	/**
	 * Filtrar datos del DNI para devolver solo los campos necesarios en la vista.
	 */
	private function filterDNIData($data)
	{
		if (isset($data['data']) && is_array($data['data'])) {
			$data = $data['data'];
		}

		return [
			'dni' => $data['dni'] ?? null,
			'nombres' => $data['nombres'] ?? null,
			'ap_pat' => $data['ap_pat'] ?? null,
			'ap_mat' => $data['ap_mat'] ?? null,
			'fecha_nac' => $data['fecha_nac'] ?? null,
			'direccion' => $data['direccion'] ?? null,
			'sexo' => $data['sexo'] ?? null,
			'est_civil' => $data['est_civil'] ?? null, // Estado civil desde la API
		];
	}

	/**
	 * Descargar plantilla Excel para importación masiva
	 */
	public function descargarPlantilla(Request $request)
	{
		$tipo = $request->input('tipo');

		if (!in_array($tipo, ['DNI', 'Carnet de Extranjería'])) {
			return response()->json(['error' => 'Tipo de documento inválido'], 400);
		}

		try {
			// Definir nombres de archivo según el tipo
			if ($tipo === 'DNI') {
				$filename = 'plantilla_importacion_dni.xlsm';
			} else {
				$filename = 'plantilla_importacion_carnet_extranjeria.xlsm';
			}

			// Ruta donde están guardadas las plantillas
			$filePath = storage_path('app/plantillas/' . $filename);

			// Verificar si el archivo existe
			if (!file_exists($filePath)) {
				Log::error("Plantilla no encontrada: $filePath");
				return response()->json([
					'error' => "La plantilla no se encuentra. Por favor, coloca el archivo '$filename' en la carpeta: storage/app/plantillas/"
				], 404);
			}

			// Devolver el archivo
			return response()->download($filePath, $filename, [
				'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			]);

		} catch (\Exception $e) {
			Log::error('Error descargando plantilla: ' . $e->getMessage());
			return response()->json(['error' => 'Error al descargar la plantilla: ' . $e->getMessage()], 500);
		}
	}

	/**
	 * Importar postulantes desde archivo Excel
	 */
	public function importar(Request $request)
	{
		$request->validate([
			'archivo_excel' => 'required|file|mimes:xlsm,xlsx|max:10240',
			'tipo_documento' => 'required|in:DNI,Carnet de Extranjería',
			'convocatoria_id' => 'nullable|integer',
		]);

		try {
			// Verificar si PhpSpreadsheet está disponible
			if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
				return response()->json(['success' => false, 'error' => 'PhpSpreadsheet no está instalado'], 500);
			}

			$archivo = $request->file('archivo_excel');
			$tipoDoc = $request->input('tipo_documento');
			$convocatoriaId = $request->input('convocatoria_id');

			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$spreadsheet = $reader->load($archivo->getRealPath());
			$sheet = $spreadsheet->getActiveSheet();
			$rows = $sheet->toArray();

			$exitosos = 0;
			$errores = 0;
			$detallesErrores = [];

			// Validar encabezados de la primera fila
			if (count($rows) < 2) {
				return response()->json([
					'success' => false,
					'error' => 'El archivo Excel está vacío o no contiene datos.'
				], 400);
			}

			$headerRow = array_map('trim', array_map('strtoupper', $rows[0]));

			// Definir encabezados esperados según tipo de documento
			if ($tipoDoc === 'DNI') {
				$expectedHeaders = [
					'NUMERO DNI',
					'CELULAR LLAMADAS',
					'CELULAR  WHATSAPP',
					'CORREO ELECTRONICO',
					'DEPARTAMENTO',
					'PROVINCIA',
					'DISTRITO',
					'EXPERIENCIA CALL CENTER',
					'DISCAPACIDAD',
					'TIPO DISCAPACIDAD (RELLENAR SI MARCÓ "SI")',
					'TIPO CONTRATO',
					'MODALIDAD DE TRABAJO',
					'HORARIO'
				];
			} else {
				// Carnet de Extranjería
				$expectedHeaders = [
					'NOMBRE',
					'APELLIDO PATERNO',
					'APELLIDO MATERNO',
					'N° CARNET EXTRANJERÍA',
					'FECHA NAC. (AÑO/MES/DÍA)',
					'SEXO',
					'DIRECCIÓN',
					'ESTADO CIVIL',
					'NACIONALIDAD',
					'CELULAR LLAMADAS',
					'CELULAR  WHATSAPP',
					'CORREO ELECTRONICO',
					'DEPARTAMENTO',
					'PROVINCIA',
					'DISTRITO',
					'EXPERIENCIA CALL CENTER',
					'DISCAPACIDAD',
					'TIPO DISCAPACIDAD (RELLENAR SI MARCÓ "SI")',
					'TIPO CONTRATO',
					'MODALIDAD DE TRABAJO',
					'HORARIO'
				];
			}

			// Validar que los encabezados coincidan
			$missingHeaders = [];
			foreach ($expectedHeaders as $index => $expectedHeader) {
				if (!isset($headerRow[$index]) || $headerRow[$index] !== $expectedHeader) {
					$columnLetter = chr(65 + $index); // A, B, C, etc.
					$actualHeader = $headerRow[$index] ?? '(vacío)';
					$missingHeaders[] = "Columna $columnLetter: se esperaba '$expectedHeader', pero se encontró '$actualHeader'";
				}
			}

			if (!empty($missingHeaders)) {
				$tipoNombre = $tipoDoc === 'DNI' ? 'DNI' : 'Carnet de Extranjería';
				return response()->json([
					'success' => false,
					'error' => "El archivo no tiene el formato correcto para importación de $tipoNombre. Por favor descarga y usa la plantilla proporcionada.",
					'detalles' => $missingHeaders
				], 400);
			}

			// Empezar desde la fila 2 (índice 1)
			for ($i = 1; $i < count($rows); $i++) {
				$row = $rows[$i];

				// Saltar filas vacías
				if (empty(array_filter($row))) {
					continue;
				}

				try {
					if ($tipoDoc === 'DNI') {
						// Para DNI: leer DNI de columna A (índice 0) y consultar API
						// Luego leer datos adicionales de las otras columnas
						$dni = trim($row[0] ?? ''); // Columna A: NUMERO DNI

						if (empty($dni)) {
							$errores++;
							$detallesErrores[] = "Fila " . ($i + 1) . ": DNI vacío";
							continue;
						}

						// Verificar si ya existe en postulantes activos
						$existingPostulante = DB::table('raz_postulantes')
							->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
							->where('raz_postulantes.dni', $dni)
							->where('raz_convocatorias.estado', '!=', 'Cancelada')
							->select('raz_postulantes.nombres', 'raz_postulantes.ap_pat', 'raz_postulantes.ap_mat')
							->first();

						if ($existingPostulante) {
							$errores++;
							$nombreCompleto = trim("{$existingPostulante->nombres} {$existingPostulante->ap_pat} {$existingPostulante->ap_mat}");
							$detallesErrores[] = "Fila " . ($i + 1) . ": DNI $dni ($nombreCompleto) ya existe";
							continue;
						}

						// Consultar API
						$resp = Http::asForm()->post('https://buscardniperu.com/wp-admin/admin-ajax.php', [
							'dni' => $dni,
							'action' => 'consulta_dni_api',
							'tipo' => 'dni',
							'pagina' => '1',
						]);

						if (!$resp->successful()) {
							$errores++;
							$detallesErrores[] = "Fila " . ($i + 1) . ": No se pudo obtener datos del DNI $dni";
							continue;
						}

						$apiData = $resp->json();
						$filtered = $this->filterDNIData($apiData);

						if (!$filtered['nombres'] || !$filtered['ap_pat'] || !$filtered['ap_mat']) {
							$errores++;
							$detallesErrores[] = "Fila " . ($i + 1) . ": Datos incompletos del DNI $dni";
							continue;
						}

						// Leer datos adicionales del Excel
						// Columna B: CELULAR LLAMADAS, C: CELULAR WHATSAPP, D: CORREO, etc.
						$celular = trim($row[1] ?? ''); // Columna B
						$whatsapp = trim($row[2] ?? ''); // Columna C
						$correo = trim($row[3] ?? ''); // Columna D
						$departamento = trim($row[4] ?? ''); // Columna E
						$provincia = trim($row[5] ?? ''); // Columna F
						$distrito = trim($row[6] ?? ''); // Columna G
						$experiencia = strtolower(trim($row[7] ?? 'no')); // Columna H
						$discapacidad = strtolower(trim($row[8] ?? 'no')); // Columna I
						$tipoDiscapacidad = trim($row[9] ?? ''); // Columna J
						$tipoContrato = trim($row[10] ?? ''); // Columna K
						$modalidadTrabajo = trim($row[11] ?? 'presencial'); // Columna L
						$horario = trim($row[12] ?? '0'); // Columna M

						// Preparar datos para insertar
						$data = [
							'tipo_documento' => 'DNI',
							'dni' => $dni,
							'nombres' => $filtered['nombres'],
							'ap_pat' => $filtered['ap_pat'],
							'ap_mat' => $filtered['ap_mat'],
							'fecha_nac' => $filtered['fecha_nac'] ?: date('Y-m-d'),
							'direccion' => $filtered['direccion'] ?: '',
							'est_civil' => $filtered['est_civil'] ?: null,
							'sexo' => $filtered['sexo'] ?: '0',
							'nacionalidad' => null,
							'celular' => $celular,
							'whatsapp' => $whatsapp,
							'correo' => $correo,
							'provincia' => $provincia ?: null,
							'distrito' => $distrito ?: null,
							'experiencia_callcenter' => ($experiencia === 'si' || $experiencia === 'sí') ? 'si' : 'no',
							'discapacidad' => ($discapacidad === 'si' || $discapacidad === 'sí') ? 'si' : 'no',
							'tipo_discapacidad' => $tipoDiscapacidad ?: null,
							'tipo_contrato' => $tipoContrato ?: null,
							'modalidad_trabajo' => $modalidadTrabajo ?: 'presencial',
							'tipo_gestion' => $horario ?: '0',
							'convocatoria_id' => $convocatoriaId,
						];

					} else {
						// Para Carnet de Extranjería: leer todos los datos de la fila
						// Estructura: A=NOMBRE, B=APELLIDO PATERNO, C=APELLIDO MATERNO, D=CARNET, etc.
						$nombres = trim($row[0] ?? ''); // Columna A: NOMBRE
						$apPat = trim($row[1] ?? ''); // Columna B: APELLIDO PATERNO
						$apMat = trim($row[2] ?? ''); // Columna C: APELLIDO MATERNO
						$carnet = trim($row[3] ?? ''); // Columna D: N° CARNET EXTRANJERÍA

						// Validar campos obligatorios
						if (empty($nombres) || empty($apPat) || empty($apMat)) {
							$errores++;
							$detallesErrores[] = "Fila " . ($i + 1) . ": Faltan nombres o apellidos";
							continue;
						}

						if (empty($carnet)) {
							$errores++;
							$detallesErrores[] = "Fila " . ($i + 1) . ": Carnet de Extranjería vacío";
							continue;
						}

						// Verificar si ya existe en postulantes activos
						$existingPostulante = DB::table('raz_postulantes')
							->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
							->where('raz_postulantes.dni', $carnet)
							->where('raz_convocatorias.estado', '!=', 'Cancelada')
							->select('raz_postulantes.nombres', 'raz_postulantes.ap_pat', 'raz_postulantes.ap_mat')
							->first();

						if ($existingPostulante) {
							$errores++;
							$nombreCompleto = trim("{$existingPostulante->nombres} {$existingPostulante->ap_pat} {$existingPostulante->ap_mat}");
							$detallesErrores[] = "Fila " . ($i + 1) . ": Carnet $carnet ($nombreCompleto) ya existe";
							continue;
						}

						// Leer fecha de nacimiento (formato DÍA/MES/AÑO)
						$fechaNacRaw = trim($row[4] ?? ''); // Columna E: FECHA NAC. (DÍA/MES/AÑO)
						$fechaNac = null;
						if (!empty($fechaNacRaw)) {
							try {
								// Intentar parsear formato D/M/Y
								if (strpos($fechaNacRaw, '/') !== false) {
									$parts = explode('/', $fechaNacRaw);
									if (count($parts) === 3) {
										$fechaNac = Carbon::createFromFormat('d/m/Y', $fechaNacRaw)->format('Y-m-d');
									}
								} else {
									// Intentar otros formatos
									$fechaNac = date('Y-m-d', strtotime($fechaNacRaw));
								}
							} catch (\Exception $e) {
								$fechaNac = date('Y-m-d');
							}
						}
						if (!$fechaNac) {
							$fechaNac = date('Y-m-d');
						}

						// Leer sexo (puede venir como texto o número)
						$sexoRaw = trim($row[5] ?? ''); // Columna F: SEXO
						$sexo = '0';
						if (!empty($sexoRaw)) {
							$sexoLower = strtolower($sexoRaw);
							if ($sexoLower === 'masculino' || $sexoLower === 'm' || $sexoRaw === '1') {
								$sexo = '1';
							} elseif ($sexoLower === 'femenino' || $sexoLower === 'f' || $sexoRaw === '2') {
								$sexo = '2';
							}
						}

						$data = [
							'tipo_documento' => 'Carnet de Extranjería',
							'dni' => $carnet,
							'nombres' => $nombres,
							'ap_pat' => $apPat,
							'ap_mat' => $apMat,
							'fecha_nac' => $fechaNac,
							'direccion' => trim($row[6] ?? ''), // Columna G: DIRECCIÓN
							'est_civil' => !empty($row[7]) ? strtolower(trim($row[7])) : null, // Columna H: ESTADO CIVIL
							'sexo' => $sexo,
							'nacionalidad' => trim($row[8] ?? '') ?: null, // Columna I: NACIONALIDAD
							'celular' => trim($row[9] ?? ''), // Columna J: CELULAR LLAMADAS
							'whatsapp' => trim($row[10] ?? ''), // Columna K: CELULAR WHATSAPP
							'correo' => trim($row[11] ?? ''), // Columna L: CORREO ELECTRONICO
							'provincia' => trim($row[13] ?? '') ?: null, // Columna N: PROVINCIA (saltamos M que es DEPARTAMENTO)
							'distrito' => trim($row[14] ?? '') ?: null, // Columna O: DISTRITO
							'experiencia_callcenter' => !empty($row[15]) ? (strtolower(trim($row[15])) === 'si' || strtolower(trim($row[15])) === 'sí' ? 'si' : 'no') : 'no', // Columna P
							'discapacidad' => !empty($row[16]) ? (strtolower(trim($row[16])) === 'si' || strtolower(trim($row[16])) === 'sí' ? 'si' : 'no') : 'no', // Columna Q
							'tipo_discapacidad' => trim($row[17] ?? '') ?: null, // Columna R
							'tipo_contrato' => trim($row[18] ?? '') ?: null, // Columna S
							'modalidad_trabajo' => trim($row[19] ?? 'presencial'), // Columna T
							'tipo_gestion' => trim($row[20] ?? '0'), // Columna U: HORARIO
							'convocatoria_id' => $convocatoriaId,
						];
					}

					// Formatear fecha_nac (ya viene formateada para Carnet, solo validar para DNI)
					if (!empty($data['fecha_nac'])) {
						$fecha = $data['fecha_nac'];
						if (strlen($fecha) > 10)
							$fecha = substr($fecha, 0, 10);

						// Si ya está en formato Y-m-d, usarlo directamente
						if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
							try {
								$data['fecha_nac'] = (string) Carbon::createFromFormat('Y-m-d', $fecha)->toDateString();
							} catch (\Exception $e) {
								$data['fecha_nac'] = date('Y-m-d');
							}
						} else {
							// Intentar otros formatos
							try {
								$data['fecha_nac'] = (string) Carbon::parse($fecha)->toDateString();
							} catch (\Exception $e) {
								$data['fecha_nac'] = date('Y-m-d');
							}
						}
					}

					// Convertir campos vacíos a null
					$nullableFields = ['est_civil', 'nacionalidad', 'provincia', 'distrito', 'tipo_discapacidad', 'tipo_contrato'];
					foreach ($nullableFields as $field) {
						if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
							$data[$field] = null;
						}
					}

					// Insertar en la base de datos
					$columns = array_keys($data);
					$placeholders = [];
					$bindings = [];
					foreach ($columns as $col) {
						if ($col === 'fecha_nac') {
							$placeholders[] = "CONVERT(date, ?, 23)";
							$bindings[] = $data[$col];
						} else {
							$placeholders[] = "?";
							$bindings[] = $data[$col];
						}
					}

					$sql = 'INSERT INTO [raz_postulantes] (' . implode(', ', $columns) . ', created_at, updated_at) VALUES (' . implode(', ', $placeholders) . ', GETDATE(), GETDATE())';
					DB::insert($sql, $bindings);

					$exitosos++;
				} catch (\Exception $e) {
					$errores++;
					$detallesErrores[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
					Log::error("Error importando fila " . ($i + 1) . ": " . $e->getMessage());
				}
			}

			return response()->json([
				'success' => true,
				'exitosos' => $exitosos,
				'errores' => $errores,
				'total' => $exitosos + $errores,
				'detalles_errores' => $detallesErrores,
			]);

		} catch (\Exception $e) {
			Log::error('Error importando archivo: ' . $e->getMessage());
			return response()->json(['success' => false, 'error' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
		}
	}

	/**
	 * Transferir todos los postulantes de una convocatoria a Postulantes_En_Formacion (Capacitación)
	 */
	public function capacitarConvocatoria($convocatoriaId)
	{
		try {
			// Obtener todos los postulantes de la convocatoria con información necesaria
			$postulantes = DB::table('raz_postulantes')
				->join('raz_convocatorias', 'raz_postulantes.convocatoria_id', '=', 'raz_convocatorias.id')
				->leftJoin('raz_convocatorias_detalles', 'raz_convocatorias.id', '=', 'raz_convocatorias_detalles.convocatoria_id')
				->where('raz_postulantes.convocatoria_id', $convocatoriaId)
				->select(
					'raz_postulantes.id',
					'raz_postulantes.dni',
					'raz_postulantes.nombres',
					'raz_postulantes.ap_pat',
					'raz_postulantes.ap_mat',
					'raz_postulantes.celular',
					'raz_postulantes.fecha_nac',
					'raz_postulantes.experiencia_callcenter',
					'raz_convocatorias.campana',
					'raz_convocatorias.reclutadores_asignados',
					'raz_convocatorias.fecha_inicio_capacitacion',
					'raz_convocatorias_detalles.modalidad_trabajo'
				)
				->get();

			if ($postulantes->isEmpty()) {
				return response()->json(['success' => false, 'error' => 'No hay postulantes en esta convocatoria'], 404);
			}

			// Obtener DNI del primer reclutador asignado (será el mismo para todos)
			$reclutadores = json_decode($postulantes[0]->reclutadores_asignados, true);
			$dniCapacitador = null;
			if (is_array($reclutadores) && count($reclutadores) > 0) {
				$dniCapacitador = $reclutadores[0];
			}

			// Mapear modalidad_trabajo a ModalidadID
			$modalidadId = null;
			if ($postulantes[0]->modalidad_trabajo) {
				$modalidadLower = strtolower(trim($postulantes[0]->modalidad_trabajo));
				if ($modalidadLower === 'presencial') {
					$modalidadId = 1;
				} elseif ($modalidadLower === 'remoto' || $modalidadLower === 'remote') {
					$modalidadId = 2;
				}
			}

			$campanaId = $postulantes[0]->campana;
			$transferidos = 0;
			$yaExistentes = 0;
			$errores = [];

			// Procesar cada postulante
			foreach ($postulantes as $postulante) {
				try {
					// Verificar si ya existe en Postulantes_En_Formacion
					$exists = DB::table('Postulantes_En_Formacion')
						->where('DNI', $postulante->dni)
						->exists();

					if ($exists) {
						$yaExistentes++;
						continue;
					}

					// Preparar datos para insertar
					// Extraer solo la fecha (sin hora) de fecha_inicio_capacitacion
					$fechaInicio = null;
					if ($postulantes[0]->fecha_inicio_capacitacion) {
						$fechaInicio = date('Y-m-d', strtotime($postulantes[0]->fecha_inicio_capacitacion));
					}

					$data = [
						'DNI' => $postulante->dni,
						'Nombres' => $postulante->nombres,
						'ApellidoPaterno' => $postulante->ap_pat,
						'ApellidoMaterno' => $postulante->ap_mat,
						'Telefono' => $postulante->celular,
						'FechaNacimiento' => $postulante->fecha_nac,
						'FechaInicio' => $fechaInicio,
						'EstadoPostulante' => 'Capacitación',
						'Experiencia' => $postulante->experiencia_callcenter === 'si' ? 'SI' : 'NO',
						'DNI_Capacitador' => $dniCapacitador,
						'CampañaID' => $campanaId,
						'ModalidadID' => $modalidadId,
					];

					// Insertar en Postulantes_En_Formacion
					DB::table('Postulantes_En_Formacion')->insert($data);
					$transferidos++;

				} catch (\Exception $e) {
					$errores[] = "Error con DNI {$postulante->dni}: " . $e->getMessage();
					Log::error("Error capacitando postulante {$postulante->dni}: " . $e->getMessage());
				}
			}

			return response()->json([
				'success' => true,
				'message' => "Transferencia completada",
				'transferidos' => $transferidos,
				'ya_existentes' => $yaExistentes,
				'total' => count($postulantes),
				'errores' => $errores
			]);

		} catch (\Exception $e) {
			Log::error('Error al capacitar convocatoria: ' . $e->getMessage());
			return response()->json([
				'success' => false,
				'error' => 'Error al transferir postulantes: ' . $e->getMessage()
			], 500);
		}
	}

	/**
	 * Obtener historial de capacitaciones de un postulante por DNI
	 */
	public function getHistorialCapacitacion($dni)
	{
		try {
			// Primero obtener el historial básico
			$historial = DB::table('Postulantes_En_Formacion')
				->where('DNI', $dni)
				->select(
					'CampañaID',
					'DNI_Capacitador',
					'EstadoPostulante',
					'FechaInicio',
					'ModalidadID',
					'Experiencia'
				)
				->orderBy('FechaInicio', 'desc')
				->get();

			// Mapear cada registro para agregar nombre de campaña y modalidad
			$historial = $historial->map(function ($item) {
				// Intentar obtener nombre de campaña
				$item->NombreCampana = $item->CampañaID; // Default: mostrar ID

				try {
					// Intentar buscar con el ID como está
					$campana = DB::table('pri.Campanias')
						->where('CampañaID', $item->CampañaID)
						->first();

					// Si no encuentra, intentar con conversión a entero
					if (!$campana) {
						$campana = DB::table('pri.Campanias')
							->where('CampañaID', (int) $item->CampañaID)
							->first();
					}

					// Debug: ver qué columnas tiene la tabla
					if ($campana) {
						Log::info('Campaña encontrada:', (array) $campana);

						// Intentar diferentes variaciones del nombre de columna
						if (isset($campana->NombreCampaña)) {
							$item->NombreCampana = $campana->NombreCampaña;
						} elseif (isset($campana->NombreCampana)) {
							$item->NombreCampana = $campana->NombreCampaña;
						} elseif (isset($campana->nombre_campana)) {
							$item->NombreCampana = $campana->nombre_campana;
						} elseif (isset($campana->Nombre)) {
							$item->NombreCampana = $campana->Nombre;
						} elseif (isset($campana->nombre)) {
							$item->NombreCampana = $campana->nombre;
						} else {
							// Si no encuentra ninguna columna conocida, usar la primera columna de texto que encuentre
							$campaniaArray = (array) $campana;
							foreach ($campaniaArray as $key => $value) {
								if ($key !== 'CampañaID' && $key !== 'CampaniaID' && is_string($value)) {
									$item->NombreCampana = $value;
									Log::info("Usando columna: $key con valor: $value");
									break;
								}
							}
						}
					} else {
						Log::warning("No se encontró campaña con ID: {$item->CampañaID}");
					}
				} catch (\Exception $e) {
					Log::error('Error obteniendo nombre de campaña: ' . $e->getMessage());
				}

				// Obtener nombre del capacitador
				$item->NombreCapacitador = $item->DNI_Capacitador; // Default: mostrar DNI

				if ($item->DNI_Capacitador) {
					try {
						$empleado = DB::table('pri.empleados')
							->where('DNI', $item->DNI_Capacitador)
							->select('Nombres', 'ApellidoPaterno', 'ApellidoMaterno')
							->first();

						if ($empleado) {
							$nombreCompleto = trim(
								($empleado->Nombres ?? '') . ' ' .
								($empleado->ApellidoPaterno ?? '') . ' ' .
								($empleado->ApellidoMaterno ?? '')
							);
							$item->NombreCapacitador = $nombreCompleto ?: $item->DNI_Capacitador;
						}
					} catch (\Exception $e) {
						Log::error('Error obteniendo nombre de capacitador: ' . $e->getMessage());
					}
				}

				// Mapear modalidad
				$item->Modalidad = $item->ModalidadID == 1 ? 'Presencial' : ($item->ModalidadID == 2 ? 'Remoto' : 'N/A');
				return $item;
			});

			return response()->json([
				'success' => true,
				'data' => $historial
			]);
		} catch (\Exception $e) {
			Log::error('Error obteniendo historial de capacitación: ' . $e->getMessage());
			return response()->json([
				'success' => false,
				'error' => 'No se pudo obtener el historial: ' . $e->getMessage()
			], 500);
		}
	}
}
