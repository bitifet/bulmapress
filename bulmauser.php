#!/usr/bin/php
<?php

$format = @$argv[1];




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




$c = 0;
while (
	false !== $row = mysql_fetch_array ($query, MYSQL_ASSOC)
) {

	// Common fixings:/*{{{*/
	foreach (
		$row
		as $k => $v
	) {
		$row[$k] = trim ($v);
	};/*}}}*/

	// Specific fixings:
	// email:
	$email_fixings = array (
		'/,/m' => '.',
		'/@?hotmail\.?(?:com)?$/i' => '@hotmail.com',
		'/@?yahoo?\.?(?:com)?$/i' => '@yahoo.com',
		'/PUNTO?/i' => '.',
		'/[\."\s]*@[\.@]*|_?A_R_R_O_B_A_?|\[AT\]/i' => '@',
		'/_/' => '.',
		'/\.at\./' => '@', // "depends" on '_'
		'/\.o\.r\.g$/' => '.org',
	);
	while (
		! preg_match (
			'/^[\w\-\.\d]+?@[\w\.-]+\.\w{2,4}?$/',
			$row['user_email']
		)
		&& (@ list($pattern, $replacement) = each ($email_fixings))
		
	) {
		$row['user_email'] = preg_replace (
			$pattern,
			$replacement,
			$row['user_email']
		);
	};

	// Output:/*{{{*/
	switch ($format) {
	case 'csv':
		if ($c == 0) {
			echo "\"" . implode ('";"', array_keys ($row)) . "\"\n";
		};
		echo "\"" . implode ('";"', $row) . "\"\n";
		break;
	case 'sql':
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
		break;
	default:
		if ($format[0] == '_') {
			$fldname = substr ($format, 1);
			if ($fldname[0] == '_') {
				$fldname = substr ($fldname, 1);
				echo "[{$row[$fldname]}]\n";
			} else {
				echo "{$row[$fldname]}\n";
			};
		} else {
			die (
				"Bad format specification: ({$format})\n"
				. "   Valid formats are:\n"
				. "      - SQL (For inserting data into Wordpress DB).\n"
				. "      - CSV (For examining whole data).\n"
				. "      - _FieldName (For examining single field contents).\n"
				. "      - __FieldName (Brace-confined version of _FieldName).\n"
				. "   Valid FieldNames are:\n"
				. "      - " . implode ("\n      - ", array_keys ($row)) . "\n"
				. "\n"
			);
		};
	};/*}}}*/

	$c++;
};


