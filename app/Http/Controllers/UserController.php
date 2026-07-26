<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function create(Request $request)
    {
        // 1. Validaciones iniciales
        $validator = Validator::make($request->all(), [
            'identity' => 'required|numeric',
            'first_name' => 'required|string|max:20',
            'second_name' => 'nullable|string|max:20', 
            'first_lastname' => 'required|string|max:20', 
            'second_lastname' => 'nullable|string|max:20',
            'nacionality' => 'required|string|max:1', 
            'email' => 'required|string|email|max:50',
            'birthdate' => 'required|date_format:d/m/Y|before_or_equal:today',
            'company_name' => 'nullable|string|max:50',
            'rif_digit' => 'nullable|string|max:15', 
            'phone' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // 2. Buscar si el usuario ya existe por Identidad (cedula de identidad)
            $user = User::where('identity', $request->identity)
                        ->first();

            // CASO A: EL USUARIO YA EXISTE
            if ($user) {
                if ($user->email !== $request->email) {
                    // Validar que el nuevo correo no lo tenga OTRO usuario distinto
                    $emailTaken = User::where('email', $request->email)
                                      ->where('id', '!=', $user->id)
                                      ->exists();

                    if ($emailTaken) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'El correo electrónico ya está registrado por otro usuario.'
                        ], 400);
                    }

                    $user->email = $request->email;
                    $user->save();

                    return response()->json([
                        'status' => 'updated',
                        'message' => 'Su usuario se encuentra registrado ante el INTT, se ha actulizado su correo electronico',
                        'data' => $user
                    ], 200);
                }

                return response()->json([
                    'status' => 'exists',
                    'message' => 'Su usuario se encuentra registrado y validado ante el INTT',
                    'data' => $user
                ], 200);
            }

            // CASO B: EL USUARIO ES NUEVO
            // Verificamos si el correo ya está registrado antes de intentar crearlo
            $emailExists = User::where('email', $request->email)->exists();
            if ($emailExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El correo electrónico ingresado esta ocupado por otro usuario.'
                ], 400);
            }

            // Crear el nuevo usuario
            $newUser = User::create([
                'identity' => $request->identity,
                'first_name' => $request->first_name,
                'second_name' => $request->second_name,
                'first_lastname' => $request->first_lastname,
                'second_lastname' => $request->second_lastname,
                'nacionality' => $request->nacionality,
                'email' => $request->email,
                'birthdate' => $request->birthdate,
                'company_name' => $request->company_name,
                'rif_digit' => $request->rif_digit,
                'phone' => $request->phone,
            ]);

            return response()->json([
                'status' => 'created',
                'message' => 'Usuario registrado exitosamente.',
                'data' => $newUser
            ], 201);

        } catch (QueryException $e) {
            // Capturamos cualquier error insospechado de BD y devolvemos un mensaje seguro
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al procesar la solicitud.'
            ], 500);
        }
    }
}