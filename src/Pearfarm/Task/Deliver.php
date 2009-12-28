<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

class Pearfarm_Task_Deliver extends Pearfarm_AbstractTask {
  public function run($args) {
    if (!isset($args[2])) throw new Exception("No package filename specified. Please specify a valid package file.");

    $pkgTgzPath = getcwd() . "/{$args[2]}";

    $channel = $this->readChannelFromPackage($pkgTgzPath);
    $signatureBase64 = $this->calculatePackageSignature($pkgTgzPath);

    $ch = curl_init("http://{$channel}/upload.xml");
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
          'file'            => "@{$pkgTgzPath}",
          'signatureBase64' => $signatureBase64
          )
        );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $postResult = curl_exec($ch);
    curl_close($ch);
    print "{$postResult}\n";

    exit(0);
  }

  public function showHelp() {

  }

  public function getName() {
    return "deliver";
  }

  public function getAliases() {
    return array('push');
  }

  public function getDescription() {
    return "sends the package to pearfarm.org";
  }

  protected function readChannelFromPackage($pkgTgzPath)
  {
    require_once 'Archive/Tar.php';

    $archive = new Archive_Tar($pkgTgzPath);
    $packageXMLRaw = $archive->extractInString('package.xml');
    $packageXML = simplexml_load_string($packageXMLRaw);
    return $packageXML->channel;
  }

  // how to verify signature
  // NOTE: to get your public key from a rsa.pub style formatted string, run 'openssl rsa -in ~/.ssh/privatekey -pubout'
  // we probably want to do this on a server so people need only cat ~/.ssh/privatekey.pub and copy/paste to us
  /*
    $pubKey = openssl_get_publickey("
-----BEGIN PUBLIC KEY-----
MIIBIDANBgkqhkiG9w0BAQEFAAOCAQ0AMIIBCAKCAQEAurW+d5EKeSv/C73yYYOV
PXy1ZPqULmxwTKDVg7MzHRcB9nawFpn6NBYlOhnzzuf9XV44qjB3ItZ1fb57+J6E
zDTWrmPpBIB9POC7n0nnuHAG3NJuEO2ljDRtYyFnFLBF9rBCWV8uwWktlgRLHlua
8qM9QWMFEeDcr6CEef1dn5xHSe5dYVW5RUrYMoATXiDGu+2LICFH1PStM/bLav0/
yu0/wFdwRFzBwKDOd340fruSK95KxFU3/2yRBKY1w/My9BWS1qY3Ok9T8/kVf/IU
IFXxFAGQQcePveXv/upMFR6cNQdY15WV8TPCLR0iYZlKvQ6/GfnAz1xE/jan59lT
uQIBIw==
-----END PUBLIC KEY-----
");
    $res = openssl_verify(sha1_file($pkgTgzPath, true), base64_decode($signatureBase64), $pubKey, OPENSSL_ALGO_SHA1);
    switch ($res) {
      case 1:
        print 'CORRECT';
        break;
      case 0:
        print 'INCORRECT';
        break;
      case -1:
        print 'ERROR';
        break;
    }
    openssl_pkey_free($pubKey);
  */
  /**
   * Get a base64 encoded signature for the package being delivered.
   * 
   * @param string Path to the package.tgz file to be uploaded.
   * @return string base64-encoded signature file
   * @throws object Exception on error
   */
  protected function calculatePackageSignature($pkgTgzPath)
  {
    $binhash = sha1_file($pkgTgzPath, true);
    $keyfile = "file://{$this->config[Pearfarm_AbstractTask::CONFIG_KEYFILE]}";
    $key = openssl_get_privatekey($keyfile, prompt_silent("Password for {$this->config[Pearfarm_AbstractTask::CONFIG_KEYFILE]} [enter for none]: "));
    if ($key === false) throw new Exception("Keyfile at {$keyfile} didn't work: " . openssl_error_string());
    $signature = NULL;
    $ok = openssl_sign($binhash, $signature , $key, OPENSSL_ALGO_SHA1);
    openssl_pkey_free($key);
    $signatureBase64 = base64_encode($signature);
    return $signatureBase64;
  }
}

/**
 * From http://www.sitepoint.com/blogs/2009/05/01/interactive-cli-password-prompt-in-php/#
 * Interactively prompts for input without echoing to the terminal.
 * Requires a bash shell or Windows and won't work with
 * safe_mode settings (Uses `shell_exec`)
 */
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents(
      $vbscript, 'wscript.echo(InputBox("'
      . addslashes($prompt)
      . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
      . addslashes($prompt)
      . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}
