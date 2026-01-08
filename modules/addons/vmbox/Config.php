
<?php

function VMBOX__GetConfigData()
{
  return array(
    "VMBOX_URL"             => "https://vmbox.yourhosting.com:6002", // Client interface Address of vmbox
    "SSL_ENABLED"           => true, // prevents mitm attacks, client interface crt file must be in /modules/servers/vmbox/ssl/ folder
    "SSL_FileName"          => "client.crt", // ssl certificate file name, downloaded from vmbox config panel
    "VMBOX_Config"          => "https://vmbox.yourhosting.com:6003", // Config interface Address of vmbox
    "VMBOX_WS"              => "wss://vmbox.yourhosting.com:443", // WebSocket Server Address of vmbox, used only if "WebSocket" down below is true
    "VMBOX_TOKEN"           => "269266858904776496398617149055913990013185890477649639805546897",
    "WebSocket"             => true, // WebSocket connection between clients and vmbox
    "Log_Level"             => 0, // 0 for disabled log
  );
}