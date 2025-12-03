<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;
        $upn = $email; // untuk login LDAP via UPN (misal: user@domain.local)

        /*
        |--------------------------------------------------------------------------
        | 1. Coba login via LOCAL DATABASE (JWT)
        |--------------------------------------------------------------------------
        */
        if ($token = auth('api')->attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'message' => 'Login lokal berhasil',
                'token'   => $token,
                'type'    => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user'    => auth('api')->user(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Jika gagal → coba login via LDAP
        |--------------------------------------------------------------------------
        */

        try {
            $connection = new \LdapRecord\Connection([
                'hosts'    => config('ldap.connections.default.hosts'),
                'base_dn'  => env('LDAP_BASE_DN'),
                'username' => null,
                'password' => null,
                'port'     => env('LDAP_PORT', 389),
                'use_ssl'  => env('LDAP_SSL', false),
                'use_tls'  => env('LDAP_TLS', false),
            ]);

            // Connect ke LDAP server
            $connection->connect();

            // LDAP Login (Bind)
            $connection->auth()->bind($upn, $password);

            // Jika bind berhasil → ambil atribut user dari LDAP
            $rawLdap = $connection->getLdapConnection()->getConnection();
            $filter = "(userPrincipalName={$upn})";
            $attributes = ['displayName', 'mail', 'department', 'company', 'title'];

            $search = @ldap_search($rawLdap, env('LDAP_BASE_DN'), $filter, $attributes);
            $entries = ldap_get_entries($rawLdap, $search);

            if ($entries['count'] === 0) {
                return response()->json(['message' => 'User tidak ditemukan di LDAP'], 404);
            }

            $entry = $entries[0];

            /*
            |--------------------------------------------------------------------------
            | 3. Sync user LDAP ke Database LOCAL
            |--------------------------------------------------------------------------
            */
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'       => $entry['displayname'][0] ?? $email,
                    'username'   => explode('@', $email)[0],
                    'department' => $entry['department'][0] ?? null,
                    'password'   => bcrypt(str()->random(16)), // random karena auth via LDAP
                    'phone'      => '0',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 4. Login user melalui JWT
            |--------------------------------------------------------------------------
            */
            if (! $token = auth('api')->login($user)) {
                return response()->json([
                    'message' => 'Gagal generate token JWT',
                ], 500);
            }

            return response()->json([
                'message' => 'Login LDAP berhasil',
                'token'   => $token,
                'type'    => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user'    => $user,
            ]);

        } catch (\LdapRecord\Auth\BindException $e) {

            /*
            |--------------------------------------------------------------------------
            | 5. Jika LDAP gagal → fallback ke database lokal (lagi)
            |--------------------------------------------------------------------------
            */
            $user = User::where('email', $email)->first();

            if ($user && Hash::check($password, $user->password)) {
                if (! $token = auth('api')->login($user)) {
                    return response()->json(['message' => 'Token gagal dibuat'], 500);
                }

                return response()->json([
                    'message' => 'Login lokal berhasil',
                    'token'   => $token,
                    'type'    => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user'    => $user,
                ]);
            }

            return response()->json([
                'message' => 'Email atau password salah (LDAP & lokal gagal)',
            ], 401);
        }
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function me()
    {
        return response()->json([
            'user'        => auth('api')->user(),
            'roles'       => auth('api')->user()->getRoleNames(),
            'permissions' => auth('api')->user()->getAllPermissions()->pluck('name'),
        ]);
    }
}
