#!/bin/sh
echo ">>>>  Verificando conexão com o banco <<<<"
    while true
    do
        nc -z -v -w15 ${DB_HOST} ${DB_PORT} 2>&1 >/dev/null
        verifier=$?
        if [ 0 = $verifier ]
            then
                sleep 20
                echo "Iniciando a instalação do i-Educar via script Softagon"
                composer new-install --no-interaction
                break
            else
                echo "Banco de dados não respondeu, tentando novamente..."
                sleep 5
        fi
    done
