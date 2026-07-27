<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function create(Request $request)
    {
        // validaciones
        $validator = Validator::make($request->all(), [
            'identity' => 'required|string',
            'first_name' => 'required|string|max:20',
            'second_name' => 'nullable|string|max:20',
            'first_lastname' => 'required|string|max:20',
            'second_lastname' => 'nullable|string|max:20',
            'nacionality' => 'required|string|max:1',
            'email' => 'required|string|email|max:50',
            'birthdate' => 'required|date_format:d/m/Y|before_or_equal:today',
            'company_name' => 'nullable|string|max:50',
            'rif_digit' => 'nullable|string|max:15',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $cedula = str_replace(' ', '', $request->identity);
            $user = User::where('identity', $cedula)->first();

            // 1er caso: el usuario ya existe
            if ($user) {
                $emailBd = strtoupper(trim($user->email));
                $emailRequest = strtoupper(trim($request->email));

                if ($emailBd !== $emailRequest) {
                    return response()->json([
                        'status' => 409,
                        'message' => 'Hemos detectado que ya se encuentra registrado, pero con un correo diferente. ¿Desea actualizar su correo electrónico actual (' . $emailBd . ') por el nuevo (' . $emailRequest . ')?',
                        'data' => $user
                    ], 409);
                }

                // Si los correos son IGUALES
                return response()->json([
                    'status' => 200,
                    'message' => 'Su usuario se encuentra registrado y validado ante el INTT.',
                    'data' => $user
                ], 200);
            }

            // 2do caso: el usuario no existe (Creación de nuevo usuario)
            $emailExists = User::where('email', $request->email)->exists();
            if ($emailExists) {
                return response()->json([
                    'status' => 400,
                    'message' => 'No puede registrarse con el correo de otro usuario.'
                ], 400);
            }

            $newUser = User::create([
                'identity' => $cedula,
                'first_name' => strtoupper(trim($request->first_name)),
                'second_name' => strtoupper(trim($request->second_name)),
                'first_lastname' => strtoupper(trim($request->first_lastname)),
                'second_lastname' => strtoupper(trim($request->second_lastname)),
                'nacionality' => strtoupper(trim($request->nacionality)),
                'email' => strtoupper(trim($request->email)),
                'birthdate' => $request->birthdate,
                'company_name' => strtoupper(trim($request->company_name)),
                'rif_digit' => strtoupper(trim($request->rif_digit)),
                'phone' => str_replace(' ', '', $request->phone),
            ]);

            return response()->json([
                'status' => 201,
                'message' => 'Usuario registrado exitosamente.',
                'data' => $newUser
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al procesar la solicitud.'
            ], 500);
        }
    }

    public function getUser()
    {

        try {
            $users = User::orderBy('id', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener la lista de usuarios.'
            ], 500);
        }
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|string',
            'email' => 'required|string|email|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación de datos.',
                'detalles' => $validator->errors()
            ], 422);
        }

        try {
            $cedula = str_replace(' ', '', $request->identity);
            $usuario = User::where('identity', $cedula)
                ->first();

            if (!$usuario || empty($usuario)) {
                return response()->json([
                    'error' => 'Usuario no encontrado en el sistema secundario (INTT).'
                ], 404);
            }

            $correo_viejo = strtoupper(trim($usuario->email));
            $correo_nuevo = strtoupper(trim($request->email));

            if ($correo_viejo !== $correo_nuevo) {

                $emailTaken = User::where('email', $request->email)
                    ->where('id', '!=', $usuario->id)
                    ->exists();

                if ($emailTaken) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El correo electrónico ya está registrado por otro usuario.'
                    ], 400);
                }

                // if (!$request->boolean('confirm_email_update')) {
                //     return response()->json([
                //         'status' => 409,
                //         'message' => 'Hemos detectado un correo diferente. ¿Desea actualizar su correo electrónico actual (' . $correo_viejo . ') por el nuevo (' . $correo_nuevo . ')?',
                //         'data' => $usuario
                //     ], 409);
                // }

                $usuario->email = $correo_nuevo;
                $usuario->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Se ha actualizado su correo electrónico exitosamente.',
                    'data' => $usuario
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'El correo electrónico proporcionado es el mismo que ya tiene registrado.',
                'data' => $usuario
            ], 200);


        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al procesar la actualización del correo.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}

