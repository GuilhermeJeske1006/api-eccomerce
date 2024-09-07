<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Hash, Log as FacadesLog};

class AlterarSenhaController extends Controller
{
    /**
     * @OA\PUT(
     *     path="/api/usuario/editar/senha/{id}",
     *     summary="Alterar a senha do usuário",
     *     description="Permite que um usuário altere sua senha, verificando a senha atual e confirmando a nova senha.",
     *     tags={"Usuário"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currentPassword", "newPassword", "confirmPassword"},
     *             @OA\Property(property="currentPassword", type="string", example="senhaAntiga123"),
     *             @OA\Property(property="newPassword", type="string", example="novaSenha456"),
     *             @OA\Property(property="confirmPassword", type="string", example="novaSenha456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha alterada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Senha alterada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Senha antiga incorreta ou as senhas não conferem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao alterar senha",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao alterar senha"),
     *             @OA\Property(property="erro", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     *
     * Altera a senha de um usuário.
     *
     * @param Request $request Objeto de solicitação contendo as senhas.
     * @param int $user_id ID do usuário cuja senha será alterada.
     * @return JsonResponse Retorna uma mensagem de sucesso ou erro.
     */

    public function __invoke(Request $request, int $user_id): JsonResponse
    {
        try {
            $user = User::findOrFail($user_id);

            DB::beginTransaction();

            if (!Hash::check($request->currentPassword, $user->password)) {
                return response()->json(['message' => 'Senha antiga incorreta'], 400);
            }

            if ($request->newPassword != $request->confirmPassword) {
                return response()->json(['message' => 'As senhas não conferem'], 400);
            }

            $user->update([
                'password' => bcrypt($request->newPassword),
            ]);

            DB::commit();

            return response()->json(['message' => 'Senha alterada com sucesso'], 200);
        } catch (\Throwable $th) {
            FacadesLog::error($th->getMessage());
            DB::rollBack();

            return response()->json(['message' => 'Erro ao alterar senha', 'erro' => $th->getMessage()], 500);
        }
    }
}
