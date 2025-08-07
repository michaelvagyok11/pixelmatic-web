<?php
// ===== BEÁLLÍTÁSOK =====
$recipient_email = "roth.armand@pixelmatic.hu"; // Ide írd a saját e-mail címedet!
// ========================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // SPAM szűrés (Honeypot)
    if (!empty($_POST['website-url'])) {
        http_response_code(400);
        die("Spam detected.");
    }

    // Adatok összegyűjtése és tisztítása
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    
    // Email cím validálása
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: hiba"); // Átirányítás a 'szép' URL-re
        exit;
    }

    // Tárgy beállítása
    $form_type = isset($_POST['form_type']) ? $_POST['form_type'] : 'Ismeretlen Űrlap';
    $subject = "Pixelmatic.hu: " . $form_type;

    // HTML E-mail törzsének összeállítása
    $email_body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 8px; border: 1px solid #ddd; }
            td:first-child { background-color: #f2f2f2; font-weight: bold; width: 30%; }
        </style>
    </head>
    <body>
        <h2>Új üzenet érkezett a weboldaladról!</h2>
        <p><strong>Űrlap típusa:</strong> '.htmlspecialchars($form_type, ENT_QUOTES, "UTF-8").'</p>
        <table>';

    foreach ($_POST as $key => $value) {
        // A rejtett mezőket nem jelenítjük meg az e-mailben
        if ($key == 'website-url' || $key == 'form_type') {
            continue;
        }

        // A kulcsot olvashatóbbá tesszük (pl. 'weboldal-tipusa' -> 'Weboldal tipusa')
        $label = ucfirst(str_replace('-', ' ', $key));

        // Az értéket biztonságossá tesszük
        if (is_array($value)) {
            // Ha a funkciókról van szó (tömb), akkor felsorolásként jelenítjük meg
            $field_value = implode('<br>', array_map(function($item) {
                return htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }, $value));
        } else {
            // Sima szöveges érték
            $field_value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        }
        
        $email_body .= "<tr><td>{$label}</td><td>{$field_value}</td></tr>";
    }
    
    $email_body .= '</table></body></html>';

    // HTML E-mail fejlécek
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Pixelmatic <noreply@pixelmatic.hu>\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";

    // E-mail küldése
    if (mail($recipient_email, $subject, $email_body, $headers)) {
        header("Location: koszonjuk");
    } else {
        header("Location: hiba");
    }

} else {
    http_response_code(403);
    echo "Hiba: Közvetlen hozzáférés nem engedélyezett.";
}
?>