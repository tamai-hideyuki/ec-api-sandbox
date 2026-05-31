<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function login(LoginUserRequest $request)
    {
        $user = User::active()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'メールアドレスまたはパスワードが正しくありません。'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました。']);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->only(['name', 'email', 'password']));

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->update(['is_invalid' => true]);
        $user->tokens()->delete();

        return response()->json(['message' => 'アカウントを削除しました。']);
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::active()->get(['id', 'name', 'email', 'role', 'seller_status']);

        return response()->json($users);
    }

    public function sellerApply(User $user)
    {
        $this->authorize('sellerApply', $user);

        if (in_array($user->seller_status, ['pending', 'approved'])) {
            return response()->json(['message' => 'すでに申請済みまたは承認済みです。'], 409);
        }

        $user->update(['seller_status' => 'pending']);

        return response()->json(['message' => '出品者申請を受け付けました。']);
    }

    public function sellerApprove(User $user)
    {
        $this->authorize('sellerApprove', User::class);

        if ($user->seller_status !== 'pending') {
            return response()->json(['message' => '申請中の会員のみ承認できます。'], 409);
        }

        $user->update(['role' => 'seller', 'seller_status' => 'approved']);

        return response()->json(['message' => '出品者として承認しました。']);
    }

    public function sellerReject(User $user)
    {
        $this->authorize('sellerReject', User::class);

        if ($user->seller_status !== 'pending') {
            return response()->json(['message' => '申請中の会員のみ却下できます。'], 409);
        }

        $user->update(['seller_status' => 'rejected']);

        return response()->json(['message' => '出品者申請を却下しました。']);
    }
}
