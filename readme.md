
i-educar é um produto da Portabilis

<sup>Este não é o repositório oficial</sup>

# Fácil instalação do i-educar

**Fork  SoftagonSistemas/i-educar**

```
[![Vídeo instalando](https://img.youtube.com/vi/V86jn2dOtRw/0.jpg)](https://www.youtube.com/watch?v=V86jn2dOtRw)
```

https://github.com/SoftagonSistemas/i-educar

## Pré-instalação

Para conseguirmos produzir uma versão de fácil instalação, fomos obrigados a tirar uma cópia do repositório oficial e ajustar alguns arquivos.

## Dúvidas ou melhorias

Caso queira contribuir ou sanar dúvidas, por favor escreva uma *issue* neste endereço:

https://github.com/SoftagonSistemas/i-educar/issues

## Docker compose

```version: '3'

services:

  web:
    container_name: ieducar-softagon
    image: softagon/i-educar:beta
    volumes:
      - ./:/var/www/html
    ports:
      - 8080:80
    networks:
      - softagon
    environment:
      DB_CONNECTION: 'pgsql'
      DB_HOST: 'postgres'
      DB_PORT: '5432'
      DB_DATABASE: 'ieducar'
      DB_USERNAME: 'ieducar'
      DB_PASSWORD: 'ieducar'
      API_ACCESS_KEY: 'softagon'
      API_SECRET_KEY: 'fjIniRGr2@3H'
    links:
      - postgres
      - redis
  postgres:
    container_name: ieducar-postgres
    image: postgres:alpine
    environment:
      POSTGRES_DB: ieducar
      POSTGRES_USER: ieducar
      POSTGRES_PASSWORD: ieducar
    ports:
      - 5432
    volumes:
      - pgdata:/var/lib/postgresql/data
    networks:
      - softagon
  redis:
    container_name: ieducar-redis
    image: redis:alpine
    restart: always
    ports:
      - 6379
    networks:
      - softagon
volumes:
  pgdata:
networks:
  softagon:
```
## Pós-instalação
Após executar seu docker-compose.yml , será necessário executar o script de instalação padrão. 
<sup>*Um script foi feito para testar se o banco já está rodando*</sup>

 1. Digite docker compose up -d
 2. Faça login no container **ieducar-softagon**
		`docker exec -it ieducar-softagon /bin/sh`
 3. Execute o comando ./entrypoint.sh
		`chown root.root entrypoint.sh`
		`chmod +x entrypoint.sh`
		`./entrypoint.sh`

Após executar o entrypoint.sh a instalação haverá ocorrido, e poderá acessar o serviço em http://localhost:8080/ 

## Tags  
No momento estamos na versão beta. 
[softagon/i-educar:beta](https://hub.docker.com/r/softagon/i-educar)

##### Dúvidas? Escreve para  Softagon.com.br
