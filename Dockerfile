FROM maeteno/swoole:7.2 as builder

LABEL maintainer="alan <alan@maeteno.com>"

RUN apt-get update -y
RUN apt-get install -y curl unzip git

# Install composer and add its bin to the PATH.
RUN curl -s http://getcomposer.org/installer | php \
    && echo "export PATH=${PATH}:/var/www/vendor/bin" >> ~/.bashrc \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

COPY . /app/

WORKDIR /app

RUN sed -i 's/;phar\.readonly = On/phar\.readonly = Off/g' /usr/local/php/etc/php.ini
RUN cd /app \
    && cp /app/env-example /app/.env \
    && echo "RPC_PORT=8081" >> /app/.env \
    && ./build.sh

FROM maeteno/swoole:7.2

LABEL maintainer="alan <alan@maeteno.com>"

COPY --from=builder /app/bin/app.phar /app/

WORKDIR /app

EXPOSE 8081

CMD ["php", "/app/app.phar", "start"]