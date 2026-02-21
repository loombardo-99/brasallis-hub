@echo off
echo =================================================
echo  SCRIPT DE DEPLOYMENT PARA HEROKU
echo =================================================
echo.

echo --- Adicionando todos os arquivos para o Git...
git add .
echo.

echo --- Por favor, digite a mensagem do seu commit (ex: 'Atualizando a home'):
set /p commit_message="Mensagem: "
echo.

echo --- Criando o commit...
git commit -m "%commit_message%"
echo.

echo --- Enviando o codigo para o Heroku...
echo --- Isso pode levar alguns minutos...
git push heroku main
echo.

echo --- Configurando o banco de dados no Heroku...
heroku run php configurar_banco_dados.php
echo.

echo --- Abrindo o seu site no navegador...
heroku open
echo.

echo =================================================
echo  DEPLOYMENT CONCLUIDO!
echo =================================================
echo.
pause
