<?php
// Função para obter o token JWT
function get_jwt_token($username, $password) {
    $url = 'https://seudominio.com/wp-json/jwt-auth/v1/token';

    $response = wp_remote_post($url, array(
        'body' => array(
            'username' => $username,
            'password' => $password,
        ),
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['token'])) {
        return $data['token'];
    }

    return false;
}

// Função para criar um evento
function create_event($token) {
    $url = 'https://seudominio.com/wp-json/tribe/events/v1/events';

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ),
        'body' => json_encode(array(
            'title' => 'Nome do Evento',
            'description' => 'Descrição do Evento',
            'start_date' => '2024-08-01 08:00:00',
            'end_date' => '2024-08-01 17:00:00',
            'venue' => array(
                'venue' => 'Local do Evento',
            ),
            'organizer' => array(
                'organizer' => 'Organizador do Evento',
            ),
        )),
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Algo deu errado: $error_message";
    } else {
        echo 'Evento criado com sucesso!';
    }
}

// Exemplo de uso
$username = 'seu-usuario';
$password = 'sua-senha';
$token = get_jwt_token($username, $password);

if ($token) {
    create_event($token);
} else {
    echo 'Falha ao obter token JWT';
}

// Funções auxiliares do WordPress para simular ambiente do WP
if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args = array()) {
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: " . $args['headers']['Content-Type'] . "\r\n" .
                            "Authorization: " . $args['headers']['Authorization'] . "\r\n",
                'content' => $args['body'],
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            return new WP_Error('http_request_failed', 'Falha na requisição HTTP');
        }
        return array('body' => $result);
    }

    function wp_remote_retrieve_body($response) {
        return $response['body'];
    }

    function is_wp_error($thing) {
        return ($thing instanceof WP_Error);
    }
}

class WP_Error {
    private $errors;

    public function __construct($code, $message) {
        $this->errors = array($code => array($message));
    }

    public function get_error_message() {
        $messages = array();
        foreach ($this->errors as $code => $messages_array) {
            foreach ($messages_array as $message) {
                $messages[] = $message;
            }
        }
        return implode(', ', $messages);
    }
}
?>