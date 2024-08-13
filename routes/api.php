<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\Endereco\ViaCepController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\Envio\ImprimirEtiquetaController;
use App\Http\Controllers\Envio\PagarEnvioController;
use App\Http\Controllers\Envio\RastrearEnvioController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\Frete\CalcularFreteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\Pedidos\BoletoController;
use App\Http\Controllers\Pedidos\CreditoController;
use App\Http\Controllers\Pedidos\PixController;
use App\Http\Controllers\Pedidos\WebhookController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\TamanhoController;
use App\Http\Controllers\UsuarioController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [UsuarioController::class, 'Login']);
Route::post('/usuario/criar', [UsuarioController::class, 'store']);
Route::delete('/usuario/delete/{id}', [UsuarioController::class, 'destroy']);
Route::get('/usuario/{id}', [UsuarioController::class, 'show']);
Route::get('/usuario', [UsuarioController::class, 'index']);

Route::get('/empresa/{id}', [EmpresaController::class, 'show']);
Route::post('/empresa/criar', [EmpresaController::class, 'store']);
Route::delete('/empresa/delete/{id}', [EmpresaController::class, 'destroy']);
Route::put('/empresa/editar/{id}', [EmpresaController::class, 'update']);

Route::post('/comentario/criar', [ComentarioController::class, 'store']);

Route::post('/endereco/criar', [EnderecoController::class, 'store']);
Route::put('/endereco/editar/{id}', [EnderecoController::class, 'update']);
Route::get('/endereco/cep/{cep}', ViaCepController::class);

Route::get('/categorias', [CategoriaController::class, 'index']);

Route::get('/tamanhos-para-cor/{corSelecionada}/{produtoId}', [TamanhoController::class, 'index']);


Route::get('/produto/{empresa_id}', [ProdutoController::class, 'index']);
Route::get('/produto/{empresa_id}/{id}', [ProdutoController::class, 'show']);
Route::post('/produto/criar', [ProdutoController::class, 'store']);
Route::put('/produto/{id}', [ProdutoController::class, 'update']);

Route::post('/pagamento/credito', CreditoController::class);
Route::post('/pagamentos/notificacao', WebhookController::class);
Route::post('/pagamento/pix', PixController::class);
Route::post('/pagamento/boleto', BoletoController::class);


Route::get('/pedidos/{id}', [PedidoController::class, 'index']);
Route::get('/pedido/{id}', [PedidoController::class, 'show']);
Route::put('/pedido/{id}', [PedidoController::class, 'update']);

Route::post('/calculate-frete', CalcularFreteController::class);
Route::post('/envio/frete', [EnvioController::class, 'store']);
Route::get('/envio/frete', [EnvioController::class, 'index']);
Route::get('/envio/frete', [EnvioController::class, 'index']);
Route::post('/envio/pagar', PagarEnvioController::class);
Route::post('/envio/imprimir-etiqueta', ImprimirEtiquetaController::class);
Route::post('/envio/rastrear', RastrearEnvioController::class);


