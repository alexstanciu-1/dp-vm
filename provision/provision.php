<?php

chdir(__DIR__);

echo 'provision.php', "\n";
file_put_contents("test.txt", date("Y-m-d H:i:s") . "\npid: " . getmypid());

$ServerName = 'localhost';
$ServerAlias = '';
$User = 'desc_prog';
$DocumentRoot = '/home/desc_prog/apps';
$HomeRoot = '/home/desc_prog';
$FPM_Pool = $User;

if (is_dir($HomeRoot))
	# fix some permissions if broken
	echo shell_exec("chown desc_prog:desc_prog {$HomeRoot}");

echo shell_exec("a2dissite 000-default");
echo shell_exec("a2dissite default-ssl");
echo shell_exec("a2dissite 000-default");

mkdir($DocumentRoot, 0770, true);
mkdir($DocumentRoot.'/public_html', 0750, true);
mkdir($HomeRoot.'/logs', 0750, true);
mkdir($HomeRoot.'/sessions', 0750, true);
# mkdir($DocumentRoot.'/instances', 0750, true);

echo shell_exec("chown desc_prog:desc_prog {$DocumentRoot}/public_html");
echo shell_exec("chown desc_prog:desc_prog {$HomeRoot}/logs");
echo shell_exec("chown desc_prog:desc_prog {$HomeRoot}/sessions");

echo shell_exec('chmod 0750 ' . $DocumentRoot);
echo shell_exec('chown desc_prog:www-data ' . $DocumentRoot);

echo shell_exec('chmod +rx '.$DocumentRoot.'/public_html');

ob_start();
require 'conf/apache.conf';
$apache_conf = ob_get_clean();
# echo "\n\n", $apache_conf, "\n\n";

file_put_contents("/etc/apache2/sites-available/desc_prog.conf", $apache_conf);

ob_start();
require 'conf/apache_provision.conf';
$apache_conf = ob_get_clean();

file_put_contents("/etc/apache2/sites-available/provision.conf", $apache_conf);

ob_start();
require 'conf/php-fpm.conf';
$php_fpm_conf = ob_get_clean();
# echo "\n\n", $php_fpm_conf, "\n\n";

file_put_contents("/etc/php/8.4/fpm/pool.d/desc_prog.conf", $php_fpm_conf);

ob_start();
require 'conf/php-fpm_provision.conf';
$php_fpm_conf = ob_get_clean();
# echo "\n\n", $php_fpm_conf, "\n\n";

file_put_contents("/etc/php/8.4/fpm/pool.d/provision.conf", $php_fpm_conf);

{
	# perms
	echo shell_exec("chmod +x {$HomeRoot} && \\
		chmod +x {$DocumentRoot} && \\
		chmod +x {$DocumentRoot}/apps && \\
		chmod +x {$DocumentRoot}/apps/*");
}

# too many restarts gives an error
# echo shell_exec("service php8.4-fpm restart");

echo shell_exec("a2ensite desc_prog");
echo shell_exec("a2ensite provision");
echo shell_exec('a2dismod php7.3');
echo shell_exec('a2dismod php8.2');
echo shell_exec('a2dismod php8.3');
echo shell_exec('a2dismod mpm_prefork');
echo shell_exec('a2dismod mpm_worker');
echo shell_exec('a2enmod mpm_event proxy proxy_fcgi setenvif suexec rewrite alias http2');
echo shell_exec('a2disconf php7.3-fpm');
echo shell_exec('a2disconf php8.2-fpm');
echo shell_exec('a2disconf php8.3-fpm');
echo shell_exec('a2enconf php8.4-fpm');

{
	# add Listen 8080 
	$c = file_get_contents("/etc/apache2/ports.conf");
	if (!preg_match("/(?:^|\\n)\\s*Listen\\s+8080\\b/uis", $c)) {
		file_put_contents("/etc/apache2/ports.conf", $c . "\nListen 8080\n");
	}

	# append -R to the command line
	$c = file_get_contents("/lib/systemd/system/php8.4-fpm.service");
	# ExecStart=/usr/sbin/php-fpm8.3 --nodaemonize --fpm-config /etc/php/8.3/fpm/php-fpm.conf -R
	if (!preg_match("/(?:^|\\n)\\s*ExecStart\\s*\\=[^\\n]*\\s*\\-R(\\s|\\n)/uis", $c)) {
		$lines = explode("\n", $c);
		foreach ($lines as &$l) {
			if (preg_match("/^\\s*ExecStart\\s*\\=/uis", $l))
				$l .= " -R";
		}
		file_put_contents("/lib/systemd/system/php8.4-fpm.service", implode("\n", $lines));
	}
}

mkdir("/_provision/web_logs/", 0755, true);
mkdir("/_provision/public_html/", 0755, true);

echo shell_exec('chmod +x /_provision/');
echo shell_exec('chmod +x /_provision/web_logs');
echo shell_exec('chmod +x /_provision/public_html');

# needed for the changes above
echo shell_exec('systemctl daemon-reload');

echo shell_exec('systemctl restart apache2');
echo shell_exec('systemctl restart php8.3-fpm');
echo shell_exec('systemctl restart php8.4-fpm');

# unlink("/home/desc_prog/public_html/index.php");
# file_put_contents("/home/desc_prog/public_html/index.php", "<?php\n"."chdir('/home/desc_prog/lib/dev-app'); require_once('/home/desc_prog/lib/runtime/main.php');");
# echo shell_exec('chown desc_prog:desc_prog /home/desc_prog/public_html/index.php');

# mkdir("/home/desc_prog/lib/", 0750);

# echo shell_exec("rsync -a --chmod=750 --chown={$User}:www-data /desc_prog/ /home/desc_prog/");
# echo shell_exec("find /home/desc_prog -type f -exec chmod 640 {} +");

echo shell_exec("rsync -a --chmod=750 --chown=root:www-data /desc_prog/vm/provision/server/public_html/ /_provision/public_html");
# echo shell_exec("find /_provision -type f -exec chmod 640 {} +");

# Alias /phpmyadmin /usr/share/phpmyadmin

# echo "\n\nworks fine!\n\n";

# /home/desc_prog/public_html/
# /home/desc_prog/project/
