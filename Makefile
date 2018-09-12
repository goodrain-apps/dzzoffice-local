dev:
	docker run -e MYSQL_HOST=47.92.168.60 -e MYSQL_PORT=20003 \
	-e MYSQL_USER=admin \
	-e MYSQL_PASS=5da22c3a \
	-d -p 8080:80 \
	--name dzzoffice -v `pwd`:/var/www/html php:7.2-apache