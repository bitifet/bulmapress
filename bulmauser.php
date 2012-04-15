#!/usr/bin/php
<?php


$db = mysql_connect (
	'localhost',
	'root'
);

$query = mysql_query ('use bulma');

$query = mysql_query (
	"select 
		coalesce (
			fecha_alta_autor,
			(select min(fecha_alta_noticia) from bul_tbl_noticias)
		) as user_registered,
		email_autor as user_email,
		nombre_autor as user_nicename,
		apodo_autor as user_login,
		md5(password_autor) as user_pass
	from bul_tbl_autores
	"
);

while (
	false !== $row = mysql_fetch_array ($query, MYSQL_ASSOC)
) {
	echo "update wp_users set ";

	$sep = '';
	foreach (
		$row
		as $k => $v
	) {
		echo "{$sep}{$k} = '" . mysql_escape_string ($v) . "'";
		$sep = ', ';
	};
	echo " where user_email = {$row['user_email']};\n";

};


