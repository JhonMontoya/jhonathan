<?php
// Mejoras de seguridad y validación del formulario de contacto
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([ 'error' => 'Método no permitido' ]);
  exit();
}

function sanitize($str) {
  return htmlspecialchars(strip_tags(trim($str)));
}

$receiving_email_address = 'jhonathan.guerrero.96@gmail.com';

$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
  if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
    http_response_code(400);
    echo json_encode([ 'error' => "Campo requerido: $field" ]);
    exit();
  }
}

$name = sanitize($_POST['name']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$subject = sanitize($_POST['subject']);
$message = sanitize($_POST['message']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode([ 'error' => 'Email inválido' ]);
  exit();
}

if (file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php')) {
  include($php_email_form);
} else {
  http_response_code(500);
  echo json_encode([ 'error' => 'No se pudo cargar la librería del formulario de email.' ]);
  exit();
}

$contact = new PHP_Email_Form;
$contact->ajax = true;
$contact->to = $receiving_email_address;
$contact->from_name = $name;
$contact->from_email = $email;
$contact->subject = $subject;

// Puedes configurar SMTP aquí si es necesario usando variables de entorno por seguridad
/*
$contact->smtp = array(
  'host' => getenv('SMTP_HOST'),
  'username' => getenv('SMTP_USER'),
  'password' => getenv('SMTP_PASS'),
  'port' => getenv('SMTP_PORT'),
);
*/

$contact->add_message($name, 'From');
$contact->add_message($email, 'Email');
$contact->add_message($message, 'Message', 10);

$result = $contact->send();
if ($result) {
  echo json_encode([ 'success' => true, 'message' => 'Mensaje enviado correctamente.' ]);
} else {
  http_response_code(500);
  echo json_encode([ 'error' => 'No se pudo enviar el mensaje, inténtalo más tarde.' ]);
}
?>
