<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
	 * Verifica si un DNI ya existe en la tabla de postulantes
	 */
	public function checkDNI(Request $request)
	{
		$request->validate(['dni' => 'required|string']);
		$dni = $request->input('dni');
		$exists = DB::table('raz_postulantes')->where('dni', $dni)->exists();
		return response()->json(['exists' => $exists]);
	}

	/**
	 * Obtener postulantes asociados a una convocatoria
	 */
	public function byConvocatoria($convocatoriaId)
	{
		try {
			$postulantes = DB::table('raz_postulantes')
				->where('convocatoria_id', $convocatoriaId)
				->select('id','dni','nombres','ap_pat','ap_mat','celular','correo','created_at')
				->orderBy('created_at','desc')
				->get();

			return response()->json(['success' => true, 'data' => $postulantes]);
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
				'dni' => 'required|string|unique:raz_postulantes',
				'nombres' => 'required|string',
				'ap_pat' => 'required|string',
				'ap_mat' => 'required|string',
				'celular' => 'required|string',
				'correo' => 'required|email',
				'convocatoria_id' => 'nullable|integer',
			]);

			$data = [
				'dni' => $request->input('dni'),
				'nombres' => $request->input('nombres'),
				'ap_pat' => $request->input('ap_pat'),
				'ap_mat' => $request->input('ap_mat'),
				'fecha_nac' => $request->input('fecha_nac') ?: date('Y-m-d'),
				'direccion' => $request->input('direccion') ?: '',
				'sexo' => $request->input('sexo') ?: '0',
				'celular' => $request->input('celular'),
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
				if (strlen($fecha) > 10) $fecha = substr($fecha,0,10);
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

			if ($inserted) return response()->json(['success' => true]);
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
			if (!$p) return response()->json(['success' => false, 'error' => 'No existe postulante'], 404);
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
				'dni' => 'required|string|unique:raz_postulantes',
				'nombres' => 'required|string',
				'ap_pat' => 'required|string',
				'ap_mat' => 'required|string',
				'fecha_nac' => 'required|date',
				'direccion' => 'required|string',
				'sexo' => 'required|string',
				'celular' => 'required|string',
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
				'dni','nombres','ap_pat','ap_mat','fecha_nac','direccion','sexo',
				'celular','correo','provincia','distrito','experiencia_callcenter',
				'discapacidad','tipo_discapacidad','tipo_contrato','modalidad_trabajo','tipo_gestion','convocatoria_id'
			]);

			if (isset($data['fecha_nac']) && !empty($data['fecha_nac'])) {
				$fecha = $data['fecha_nac'];
				if (strlen($fecha) > 10) $fecha = substr($fecha, 0, 10);
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
		];
	}
}

