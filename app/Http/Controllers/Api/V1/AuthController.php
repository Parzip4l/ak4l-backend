<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Sesuaikan namespace
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Connection;
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

        /*
        |--------------------------------------------------------------------------
        | 1. Coba login via LOCAL DATABASE (Cek user lokal dulu biar cepat)
        |--------------------------------------------------------------------------
        */
        if ($token = auth('api')->attempt(['email' => $email, 'password' => $password])) {
            return $this->respondWithToken($token, auth('api')->user(), 'Login lokal berhasil');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Jika gagal lokal → Coba login via LDAP
        |--------------------------------------------------------------------------
        */
        try {
            // SETUP KONEKSI (Gunakan config(), JANGAN env())
            $connection = new Connection([
                'hosts'    => config('ldap.connections.default.hosts'),
                'base_dn'  => config('ldap.connections.default.base_dn'),
                'username' => config('ldap.connections.default.username'), // User Admin utk searching
                'password' => config('ldap.connections.default.password'), // Pass Admin utk searching
                'port'     => config('ldap.connections.default.port', 389),
            ]);

            // Buka Koneksi sebagai Admin dulu untuk mencari user
            $connection->connect();

            // CARI USER DI LDAP (Pakai Query Builder LdapRecord, bukan ldap_search manual)
            // Kita cari berdasarkan 'mail' atau 'userPrincipalName'
            $ldapUser = $connection->query()
                ->where('mail', '=', $email)
                ->orWhere('userPrincipalName', '=', $email)
                ->first();

            if (! $ldapUser) {
                return response()->json(['message' => 'User tidak ditemukan di LDAP'], 404);
            }

            // VERIFIKASI PASSWORD USER (Auth Attempt)
            // Ini akan mencoba login menggunakan DN user yang ketemu tadi & password inputan
            if ($connection->auth()->attempt($ldapUser->getDn(), $password)) {
                
                /*
                |--------------------------------------------------------------------------
                | 3. Sync user LDAP ke Database LOCAL
                |--------------------------------------------------------------------------
                */
                
                // Ambil atribut dengan aman
                $displayName = $ldapUser->getFirstAttribute('displayname') ?? $email;
                $department  = $ldapUser->getFirstAttribute('department') ?? '-';
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'       => $displayName,
                        'username'   => explode('@', $email)[0],
                        'department' => $department,
                        'password'   => bcrypt(str()->random(16)), // Password acak di DB lokal
                        'phone'      => '0',
                    ]
                );

                // Update data jika user sudah ada (opsional, biar data selalu fresh)
                $user->update([
                    'name'       => $displayName,
                    'department' => $department,
                ]);

                /*
                |--------------------------------------------------------------------------
                | 4. Generate Token JWT
                |--------------------------------------------------------------------------
                */
                if (! $token = auth('api')->login($user)) {
                    return response()->json(['message' => 'Gagal generate token JWT'], 500);
                }

                return $this->respondWithToken($token, $user, 'Login LDAP berhasil');
            } else {
                // Password LDAP Salah
                return response()->json(['message' => 'Password salah (LDAP)'], 401);
            }

        } catch (\LdapRecord\Auth\BindException $e) {
            // Error koneksi ke server LDAP (Admin credentials salah atau server down)
            return response()->json(['message' => 'Gagal koneksi LDAP: ' . $e->getMessage()], 500);
            
        } catch (\Exception $e) {
            // Error umum lainnya
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Helper function biar rapi
    protected function respondWithToken($token, $user, $message)
    {
        return response()->json([
            'message' => $message,
            'token'   => $token,
            'type'    => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'    => $user,
        ]);
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
