<?php

use App\Http\Controllers\Endereco\ViaCepController;
use App\Http\Controllers\Envio\{ImprimirEtiquetaController, PagarEnvioController, RastrearEnvioController};
use App\Http\Controllers\Frete\CalcularFreteController;
use App\Http\Controllers\Imagem\ExcluirImagemController;
use App\Http\Controllers\Password\AlterarSenhaController;
use App\Http\Controllers\Pedidos\{BoletoController, CreditoController, PixController, WebhookController};
use App\Http\Controllers\Status\{StatusEnvioController, StatusPagamentoController};
use App\Http\Controllers\{BlogController, CategoriaController, ComentarioBlogController, ComentarioController, DescontoController, EmpresaController, EnderecoController, EnvioController, GestaoPedidoController, PedidoController, ProdutoController, SobreController, TamanhoController, UsuarioController};
use App\Http\Controllers\Produto\DestaqueProdutoController;
use App\Http\Controllers\Produto\SiteProdutoController;
use Illuminate\Support\Facades\{Route};

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

Route::post('/login', [UsuarioController::class, 'Login']);
Route::post('/usuario/criar', [UsuarioController::class, 'store']);
Route::delete('/usuario/deletar/{id}', [UsuarioController::class, 'destroy']);
Route::get('/usuario/{id}', [UsuarioController::class, 'show']);
Route::get('/usuarios/{empresa_id}', [UsuarioController::class, 'index']);
Route::put('/usuario/editar/{id}', [UsuarioController::class, 'update']);

Route::put('/usuario/editar/senha/{id}', AlterarSenhaController::class);

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

Route::get('/produto/destaque/{empresa_id}', DestaqueProdutoController::class);
Route::get('/produto/site/{empresa_id}', SiteProdutoController::class);

Route::get('/produto/{empresa_id}', [ProdutoController::class, 'index']);
Route::get('/produto/{empresa_id}/{id}', [ProdutoController::class, 'show']);
Route::post('/produto/criar', [ProdutoController::class, 'store']);
Route::put('/produto/{id}', [ProdutoController::class, 'update']);
Route::delete('/produto/deletar/{id}', [ProdutoController::class, 'destroy']);

Route::post('/pagamento/credito', CreditoController::class);
Route::post('/pagamentos/notificacao', WebhookController::class);
Route::post('/pagamento/pix', PixController::class);
Route::post('/pagamento/boleto', BoletoController::class);

Route::get('/pedidos/{id}', [PedidoController::class, 'index']);
Route::get('/pedido/{id}', [PedidoController::class, 'show']);
Route::patch('/pedido/{id}', [PedidoController::class, 'update']);

Route::post('/calculate-frete', CalcularFreteController::class);
Route::post('/envio/frete', [EnvioController::class, 'store']);
Route::get('/envio/frete', [EnvioController::class, 'index']);
Route::post('/envio/pagar', PagarEnvioController::class);
Route::post('/envio/imprimir-etiqueta', ImprimirEtiquetaController::class);
Route::post('/envio/rastrear', RastrearEnvioController::class);

Route::post('/desconto', [DescontoController::class, 'store']);
Route::get('/desconto', [DescontoController::class, 'index']);
Route::put('/desconto/{id}', [DescontoController::class, 'update']);
Route::delete('/desconto/{id}', [DescontoController::class, 'destroy']);

Route::get('/blogs/{empresa_id}', [BlogController::class, 'index']);
Route::get('/blog/{id}', [BlogController::class, 'show']);
Route::post('/blog/criar', [BlogController::class, 'store']);
Route::delete('/blog/delete/{id}', [BlogController::class, 'destroy']);
Route::put('/blog/editar/{id}', [BlogController::class, 'update']);

Route::get('/comentario/blog/{blog_id}', [ComentarioBlogController::class, 'index']);
Route::get('/comentario/blog/{blog_id}/{id}', [ComentarioBlogController::class, 'show']);
Route::post('/comentario/blog/criar', [ComentarioBlogController::class, 'store']);
Route::delete('/comentario/blog/delete/{id}', [ComentarioBlogController::class, 'destroy']);
Route::put('/comentario/blog/editar/{id}', [ComentarioBlogController::class, 'update']);

Route::get('/sobre/{empresa_id}', [SobreController::class, 'show']);
Route::put('/sobre/editar/{empresa_id}', [SobreController::class, 'update']);

Route::delete('/excluir-imagem', ExcluirImagemController::class);

Route::get('/pedidos/gestao/{empresa_id}', [GestaoPedidoController::class, 'index']);

Route::get('/status-envio', StatusEnvioController::class);
Route::get('/status-pagamento', StatusPagamentoController::class);
