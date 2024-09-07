<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\{Endereco, User};
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Hash, Redis, Storage};

class UsuarioController extends Controller
{
    /**
     *  @OA\GET(
     *      path="/api/usuarios/{empresa_id}",
     *      summary="User",
     *      tags={"Usuário"},
     *       @OA\Parameter(
     *         name="empresa_id",
     *         in="path",
     *         description="ID da empresa cujos pedidos serão buscados",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *  )
     */
    public function index(int $empresa_id): JsonResponse
    {
        try {
            $usuarios = User::where('empresa_id', $empresa_id)->orderBy('created_at', 'desc')->paginate();

            $usuarios->map(function ($usuario) {
                $usuario['foto'] = Storage::disk('s3')->url($usuario['foto']);

                return $usuario;
            });

            return UsuarioResource::collection($usuarios)->response();
        } catch (\Throwable $th) {
            return response()->json(['erro' => $th->getMessage(), 'message' => 'Erro ao buscar usuarios'], Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * @OA\Post(
     *     path="/api/usuario/criar",
     *     summary="Criar Usuário",
     *     tags={"Usuário"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="endereco_id", type="integer", example=1),
     *             @OA\Property(property="cpf", type="string", example=11111111111),
     *             @OA\Property(property="telefone", type="string",  example="4792801006"),
     *             @OA\Property(property="empresa_id", type="integer", example=1),
     *             @OA\Property(property="is_master", type="boolean", example=true),
     *             @OA\Property(property="foto", type="string", format="base64", example="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requisição inválida",
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     )
     * )
     */

    public function store(StoreUsuarioRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();

            if ($data['foto']) {
                $data['foto'] = uploadBase64ImageToS3($data['foto'], 'usuarios');
            } else {
                $data['foto'] = '';
            }

            $data['password'] = Hash::make($data['password']);

            User::create($data);

            return response()->json(["message" => "Usuario cadastrado com sucesso"], 201);
        } catch (\Throwable $th) {
            return response()->json(['erro' => $th->getMessage(), 'message' => 'Erro ao cadastrar usuario'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\GET(
     *
     * path="/api/usuario/{id}",
     * summary="User show",
     * tags={"Usuário"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of user to show",
     * required=true,
     * @OA\Schema(
     * type="string"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="OK",
     * @OA\MediaType(
     * mediaType="application/json"
     * )
     * ),
     * )
     */
    public function show(string $id): UsuarioResource
    {
        $cacheKey = "usuario_{$id}";

        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            $usuarioArray = json_decode($cachedData, true);
            $usuario      = User::hydrate([$usuarioArray])->first();

            return UsuarioResource::make($usuario);
        }

        $usuario = User::findOrFail($id);

        if ($usuario->foto != null) {
            $usuario->foto = Storage::disk('s3')->url($usuario->foto);
        }

        Redis::setex($cacheKey, 3600, $usuario->toJson());

        return UsuarioResource::make($usuario);
    }
    /**
     * Show the form for editing the specified resource.
     */

    public function update(Request $request, string $id)
    {
        try {

            DB::beginTransaction();

            $usuario = User::find($id);

            if (!empty($request->foto) && $request->foto != Storage::disk('s3')->url($usuario->foto)) {
                $request['foto'] = uploadBase64ImageToS3($request['foto'], 'usuarios');
            } else {
                $request['foto'] = $usuario->logo;
            }

            $usuario->update($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Usuario atualizado com sucesso',
                'data'    => new UsuarioResource($usuario),
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao atualizar usuario',
                'erro'    => $th->getMessage(),
            ], 500);
        }
    }

    /**
     *  @OA\DELETE(
     *      path="/api/usuario/delete/{id}",
     *      summary="User delete",
     *     description="Delete user by id",
     *     operationId="deleteUser",
     *    security={{"apiAuth": {}}},
     *      tags={"Usuário"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of user to delete",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *  )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(["message" => "Usuario não encontrado"], 404);
        }

        if($usuario->foto) {
            deleteImageFromS3($usuario->foto);
        }

        $usuario->delete();

        return response()->json(["message" => "Usuario excluído com sucesso"], 200);
    }

    /**
     *  @OA\POST(
     *      path="/api/login",
     *      summary="Login",
     *      tags={"Login"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(property="email", type="string", example=""),
     *              @OA\Property(property="password", type="string", example=""),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *  )
     */
    public function Login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'email'    => 'required',
                'password' => 'required',
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                $user  = Auth::user();
                $token = $user->createToken('JWT');

                return response()->json([
                    'token' => $token,
                    'user'  => $user,
                ], 200);
            }

            return response()->json('Usuario não autenticado', 401);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

}
