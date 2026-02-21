<?php include_once 'includes/cabecalho.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="card-title">Esqueceu a Senha?</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['success'])) : ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($_GET['success']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['code'])) : ?>
                        <div class="alert alert-info" role="alert">
                            <strong>Código de verificação (simulado):</strong> <?php echo htmlspecialchars($_GET['code']); ?>
                        </div>
                        <form action="verificar_codigo.php" method="POST">
                            <div class="mb-3">
                                <label for="code" class="form-label">Código de Verificação</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Verificar Código</button>
                        </form>
                    <?php else: ?>
                        <p>Digite seu endereço de e-mail para receber um código de redefinição de senha.</p>
                        <form action="enviar_link_redefinicao.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar Código de Redefinição</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/rodape.php'; ?>