<?php
// Suporta DATABASE_URL (Heroku/Railway) ou variáveis individuais.
// Prioridade:
// 1) DATABASE_URL
// 2) variáveis individuais (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD,
//    MYSQL_HOST, RAILWAY_HOST como alternativas)
// 3) valores embutidos (fallback)

function env_first(array $names, $default = null) {
    foreach ($names as $n) {
        $v = getenv($n);
        if ($v !== false && $v !== '') return $v;
    }
    return $default;
}

// Fallbacks antigos (mantidos apenas para compatibilidade local)
$fallback = [
    // Valores seguros de fallback (não contem segredos reais)
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'catechesis',
    'username' => 'root',
    'password' => '',
];

$databaseUrl = env_first(['DATABASE_URL', 'CLEARDB_DATABASE_URL']);
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    // parse_url pode retornar false em URLs inválidas
    if ($parts !== false) {
        $host = $parts['host'] ?? $fallback['host'];
        $port = $parts['port'] ?? $fallback['port'];
        $username = $parts['user'] ?? $fallback['username'];
        $password = $parts['pass'] ?? $fallback['password'];
        // path tem leading slash
        $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : $fallback['dbname'];
    } else {
        // URL inválida: volta aos env individuais / fallback
        $host = env_first(['DB_HOST','MYSQL_HOST','RAILWAY_HOST'], $fallback['host']);
        $port = env_first(['DB_PORT','MYSQL_PORT'], $fallback['port']);
        $dbname = env_first(['DB_DATABASE','DATABASE_NAME','DB_NAME'], $fallback['dbname']);
        $username = env_first(['DB_USERNAME','MYSQL_USER','DB_USER'], $fallback['username']);
        $password = env_first(['DB_PASSWORD','MYSQL_PASSWORD','DB_PASS'], $fallback['password']);
    }
} else {
    // Variáveis individuais
    $host = env_first(['DB_HOST','MYSQL_HOST','RAILWAY_HOST'], $fallback['host']);
    $port = env_first(['DB_PORT','MYSQL_PORT'], $fallback['port']);
    $dbname = env_first(['DB_DATABASE','DATABASE_NAME','DB_NAME'], $fallback['dbname']);
    $username = env_first(['DB_USERNAME','MYSQL_USER','DB_USER'], $fallback['username']);
    $password = env_first(['DB_PASSWORD','MYSQL_PASSWORD','DB_PASS'], $fallback['password']);
}

// Normaliza porta como inteiro quando possível
if (is_string($port) && ctype_digit($port)) {
    $port = (int) $port;
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Para produção talvez prefira não imprimir sucesso
    echo "✅ Conectado ao banco com sucesso!";
} catch (PDOException $e) {
    // Em produção, exponha menos detalhes
    echo "❌ Erro ao conectar: " . $e->getMessage();
}
?>
