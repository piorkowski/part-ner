FROM httpd:2.4-alpine

RUN apk add --no-cache \
    apache2-proxy \
    apache2-proxy-html

RUN sed -i \
    -e 's/^#\(LoadModule .*mod_rewrite.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy_fcgi.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_expires.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_headers.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_deflate.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_vhost_alias.so\)/\1/' \
    /usr/local/apache2/conf/httpd.conf

RUN sed -i \
    -e 's/^#\(Include .*httpd-vhosts.conf\)/\1/' \
    /usr/local/apache2/conf/httpd.conf

EXPOSE 80 443

CMD ["httpd-foreground"]
