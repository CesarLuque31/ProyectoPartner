<?php



namespace App\Http\Controllers;



use App\Models\Postulante;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;



class PostulanteController extends Controller

{

    // API del servidor (con autenticación JWT)

    private const API_BASE = 'http://10.182.18.70:8421';



    // Mostrar formulario de insertar postulante

    public function create()

    {

        return view('talent.insertar_postulante');
    }



    /**

     * Obtener o refrescar token JWT automáticamente.

     * Cache por 9 minutos para evitar llamadas excesivas a /auth/login.

     */

    private function getJWTToken()

    {

        $cacheKey = 'api_jwt_token';



        // Si el token existe en cache y es válido, devolverlo

        if (Cache::has($cacheKey)) {

            return Cache::get($cacheKey);
        }



        try {

            $resp = Http::post(self::API_BASE . '/auth/login', [

                'usuario' => env('API_EXTERNAL_USER', 'razzluk'),

                'password' => env('API_EXTERNAL_PASS', 'CesarPartner*'),

            ]);



            if ($resp->successful()) {

                $token = $resp->json('token');



                if (!$token) {

                    throw new \Exception('Token no recibido en respuesta');
                }



                // Guardar en cache por 9 minutos (se refresca cada 10 min)

                Cache::put($cacheKey, $token, now()->addMinutes(9));



                Log::info('JWT Token obtenido exitosamente');

                return $token;
            }



            $error = $resp->json('error') ?? $resp->body();

            throw new \Exception('Auth failed: ' . $error . ' (Status: ' . $resp->status() . ')');
        } catch (\Exception $e) {

            Log::error('Error obteniendo JWT token: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Verificar si un DNI ya está registrado en la tabla raz_postulantes
     */
    public function checkDNI(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|digits:8',
        ]);

        $dni = $request->input('dni');

        try {
            $exists = DB::table('raz_postulantes')
                ->where('dni', $dni)
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Postulante ya registrado' : 'DNI disponible para registrar'
            ]);
        } catch (\Exception $e) {
            Log::error('Error verificando DNI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al verificar DNI'
            ], 500);
        }
    }


    public function consulta(Request $request)

    {

        $request->validate([

            'dni' => 'required|string|digits:8',

        ]);



        $dni = $request->input('dni');



        try {

            // Intento 1: Usar API con JWT

            Log::info("Intentando consulta DNI $dni con API del amigo...");



            try {

                $token = $this->getJWTToken();



                $resp = Http::withHeaders([

                    'Authorization' => 'Bearer ' . $token,

                    'Accept' => 'application/json',

                ])->get(self::API_BASE . '/consulta', [

                    'dni' => $dni,

                ]);



                if ($resp->successful()) {

                    $data = $resp->json('data') ?? $resp->json();

                    $filtered = $this->filterDNIData($data);

                    Log::info("✓ Consulta exitosa desde API del amigo");

                    return response()->json(['success' => true, 'data' => $filtered, 'source' => 'friend_api']);
                }
                
                Log::warning("API del amigo retornó status {$resp->status()}: " . substr($resp->body(), 0, 200));

            } catch (\Exception $jwtError) {

                Log::warning("API del amigo falló: " . $jwtError->getMessage());
            }



            // Intento 2: Usar API pública (sin autenticación) como fallback

            Log::info("Usando API pública buscardniperu.com como fallback...");



            $resp = Http::asForm()->post('https://buscardniperu.com/wp-admin/admin-ajax.php', [

                'dni' => $dni,

                'action' => 'consulta_dni_api',

                'tipo' => 'dni',

                'pagina' => '1',

            ]);



            if ($resp->successful()) {

                try {

                    $data = $resp->json();

                    $filtered = $this->filterDNIData($data);

                    Log::info("✓ Consulta exitosa desde API pública");

                    return response()->json(['success' => true, 'data' => $filtered, 'source' => 'public_api']);
                } catch (\Exception $jsonError) {

                    Log::error("API pública devolvió respuesta no-JSON: " . $jsonError->getMessage() . " - Body: " . substr($resp->body(), 0, 200));

                    return response()->json(['success' => false, 'error' => 'API pública devolvió respuesta inválida (no JSON)'], 500);
                }
            } else {

                Log::error("API pública retornó status {$resp->status()}: " . substr($resp->body(), 0, 200));

                return response()->json(['success' => false, 'error' => "API pública no disponible (HTTP {$resp->status()})"], 503);
            }
        } catch (\Exception $e) {

            Log::error('Error consulta API postulante: ' . $e->getMessage());

            return response()->json([

                'success' => false,

                'error' => 'Error al consultar: ' . $e->getMessage()

            ], 500);
        }
    }



    /**

     * Guardar postulante en DB.

     */

    public function store(Request $request)

    {

        try {

            // Validación flexible - permite que algunos campos sean opcionales

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

            ]);



            // Preparar datos para guardar

            $data = $request->only([

                'dni',
                'nombres',
                'ap_pat',
                'ap_mat',
                'fecha_nac',
                'direccion',
                'sexo',

                'celular',
                'correo',
                'provincia',
                'distrito',
                'experiencia_callcenter',

                'discapacidad',
                'tipo_discapacidad',
                'tipo_contrato',
                'modalidad_trabajo',
                'tipo_gestion'

            ]);



            // Convertir fecha al formato YYYY-MM-DD para SQL Server (DATE, no DATETIME)

            if (isset($data['fecha_nac']) && !empty($data['fecha_nac'])) {

                // Si viene como timestamp, extraer solo la fecha

                $fecha = $data['fecha_nac'];

                if (strlen($fecha) > 10) {

                    $fecha = substr($fecha, 0, 10);
                }

                // Asegurar formato YYYY-MM-DD como cadena (ISO) — SQL Server acepta este formato para DATE

                $data['fecha_nac'] = (string) \Carbon\Carbon::createFromFormat('Y-m-d', $fecha)->toDateString();
            }



            // Activar query log para capturar la consulta y bindings

            // DB::flushQueryLog();

            // DB::enableQueryLog();



            // Intentar insertar manualmente usando CONVERT(date, ?, 23) para forzar conversión en SQL Server

            $columns = array_keys($data);

            $placeholders = [];

            $bindings = [];

            foreach ($columns as $col) {

                if ($col === 'fecha_nac') {

                    // usar CONVERT(date, ?, 23) para formateo YYYY-MM-DD

                    $placeholders[] = "CONVERT(date, ?, 23)";

                    $bindings[] = $data[$col];
                } else {

                    $placeholders[] = "?";

                    $bindings[] = $data[$col];
                }
            }



            // Construir SQL: agregar created_at y updated_at usando GETDATE() para evitar envíos problemáticos

            $sql = 'INSERT INTO [raz_postulantes] (' . implode(', ', $columns) . ', created_at, updated_at) VALUES (' . implode(', ', $placeholders) . ', GETDATE(), GETDATE())';



            $inserted = DB::insert($sql, $bindings);



            $queries = DB::getQueryLog();



            if ($inserted) {

                return response()->json(['success' => true, 'message' => 'Postulante guardado exitosamente.']);
            }



            return response()->json(['success' => false, 'error' => 'No se pudo insertar postulante'], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {

            Log::error('Errores de validación: ' . json_encode($e->errors()));

            return response()->json([

                'success' => false,

                'error' => 'Errores de validación',

                'errors' => $e->errors()

            ], 422);
        } catch (\Exception $e) {

            Log::error('Error guardando postulante: ' . $e->getMessage() . ' - Stack: ' . $e->getTraceAsString());

            return response()->json([

                'success' => false,

                'error' => 'No se pudo guardar postulante'

            ], 500);
        }
    }



    /**

     * Filtrar datos del DNI para devolver solo los campos necesarios en la vista.

     * Mapea los nombres de campos de la API a los que usa la vista.

     */

    private function filterDNIData($data)

    {

        // Si viene en estructura nested (algunos casos)

        if (isset($data['data']) && is_array($data['data'])) {

            $data = $data['data'];
        }



        // Mapeo de campos: API => Formulario

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
