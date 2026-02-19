<?php
/**
 * Generador de contraseñas seguras.
 *
 * Uso:
 *   echo generate_password(16, [
 *       'upper' => true,
 *       'lower' => true,
 *       'digits' => true,
 *       'symbols' => true,
 *       'avoid_ambiguous' => true,
 *       'exclude' => 'abAB12', // caracteres a excluir
 *       'require_each' => true // garantizar al menos 1 carácter de cada categoría seleccionada
 *   ]);
 *
 * Seguridad:
 * - Usa random_int() para entropía criptográfica.
 * - Evita str_shuffle() para mezclar; se usa Fisher–Yates con random_int().
 */

class PasswordGenerator {

    private static function secure_random_int_between(int $min, int $max): int {
        // wrapper para random_int (por claridad)
        return random_int($min, $max);
    }

    private static function shuffle_secure(string $str): string {
        // Fisher-Yates shuffle usando random_int
        $arr = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($arr);
        for ($i = $n - 1; $i > 0; $i--) {
            $j = self::secure_random_int_between(0, $i);
            $tmp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $tmp;
        }
        return implode('', $arr);
    }

    /**
     * Genera una contraseña segura.
     *
     * @param int $length Longitud deseada (>=1).
     * @param array $opts Opciones:
     *    - upper (bool)  : incluir mayúsculas [A-Z]
     *    - lower (bool)  : incluir minúsculas [a-z]
     *    - digits (bool) : incluir dígitos [0-9]
     *    - symbols (bool): incluir símbolos [!@#$...]
     *    - avoid_ambiguous (bool) : evitar caracteres ambiguos (Il1O0 etc.)
     *    - exclude (string) : caracteres a excluir explícitamente
     *    - require_each (bool) : garantizar al menos 1 carácter de cada categoría seleccionada
     *
     * @return string contraseña
     * @throws InvalidArgumentException
     */
    public static function generate_password(int $length = 16, array $opts = []): string {
        if ($length < 4 || $length > 128) { // en lugar de solo mas de uno
            throw new InvalidArgumentException("La longitud debe estar entre 4 y 128 caracteres");
        }

        // Opciones por defecto
        $opts = array_merge([
            'upper' => true,
            'lower' => true,
            'digits' => true,
            'symbols' => true,
            'avoid_ambiguous' => true,
            'exclude' => '',
            'require_each' => true,
            'pattern' => '',
        ], $opts);

        // Conjuntos de caracteres
        $sets = [];
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        // símbolos comunes; puedes editar según tus políticas
        $symbols = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        // caracteres ambiguos que a veces se evitan
        $ambiguous = 'Il1O0o';

        if ($opts['upper']) $sets['upper'] = $upper;
        if ($opts['lower']) $sets['lower'] = $lower;
        if ($opts['digits']) $sets['digits'] = $digits;
        if ($opts['symbols']) $sets['symbols'] = $symbols;

        if (empty($sets)) {
            throw new InvalidArgumentException("Debe activarse al menos una categoría.");
        }

        // construir pool total y aplicar exclusiones
        $exclude_chars = $opts['exclude'];
        if ($opts['avoid_ambiguous']) {
            $exclude_chars .= $ambiguous;
        }

        // normalizar exclusions a conjunto único
        $exclude_arr = array_unique(preg_split('//u', $exclude_chars, -1, PREG_SPLIT_NO_EMPTY));
        $exclude_map = array_flip($exclude_arr);
        
        // filtrar sets
        foreach ($sets as $k => $chars) {
            $arr = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
            $filtered = array_values(array_filter($arr, function($c) use ($exclude_map) {
                return !isset($exclude_map[$c]);
            }));
            if (empty($filtered)) {
                // Si una categoría queda vacía tras exclusiones -> error
                throw new InvalidArgumentException("Categoría vacía tras exclusiones.");
            }
            $sets[$k] = implode('', $filtered);
        }

        //para patron
        if (!empty($opts['pattern'])) {
            $result = '';
            $chars = str_split($opts['pattern']);
            
            foreach ($chars as $char) {
                if ($char === 'U' && isset($sets['upper'])) {
                    $result .= $sets['upper'][self::secure_random_int_between(0, strlen($sets['upper']) - 1)];
                } elseif ($char === 'l' && isset($sets['lower'])) {
                    $result .= $sets['lower'][self::secure_random_int_between(0, strlen($sets['lower']) - 1)];
                } elseif ($char === 'd' && isset($sets['digits'])) {
                    $result .= $sets['digits'][self::secure_random_int_between(0, strlen($sets['digits']) - 1)];
                } elseif ($char === 's' && isset($sets['symbols'])) {
                    $result .= $sets['symbols'][self::secure_random_int_between(0, strlen($sets['symbols']) - 1)];
                } else {
                    $result .= $char; 
                }
            }
            return $result; 
        }

        // crear pool total concatenado
        $pool = implode('', array_values($sets));
        $password_chars = [];

        // Si require_each: garantizar al menos un carácter de cada categoría seleccionada
        if ($opts['require_each']) {
            foreach ($sets as $chars) {
                $idx = self::secure_random_int_between(0, strlen($chars) - 1);
                $password_chars[] = $chars[$idx];
            }
        }

        // Rellenar el resto de la longitud con caracteres del pool
        $needed = $length - count($password_chars);
        for ($i = 0; $i < $needed; $i++) {
            $idx = self::secure_random_int_between(0, strlen($pool) - 1);
            $password_chars[] = $pool[$idx];
        }

        // Mezclar de forma segura y devolver
        $password = implode('', $password_chars);
        return self::shuffle_secure($password);
    }

    
    /**
     * Genera múltiples contraseñas a la vez.
     *
     * @param int $count número de contraseñas
     * @param int $length longitud de cada contraseña
     * @param array $opts opciones (ver generate_password)
     * @return array lista de contraseñas
     */
    public static function generate_passwords(int $count = 5, int $length = 16, array $opts = []): array {
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = self::generate_password($length, $opts);
        }
        return $passwords;
    }

    public static function validate_password(string $password, array $reqs): array {
        $isValid = true;
        $errors = [];

        if (isset($reqs['minLength']) && strlen($password) < $reqs['minLength']) {
            $isValid = false;
            $errors[] = "La contraseña no cumple con la longitud minima";
        }
        if (isset($reqs['requireUppercase']) && $reqs['requireUppercase'] && !preg_match('/[A-Z]/', $password)) {
            $isValid = false;
            $errors[] = "Debe contener al menos una mayuscula.";
        }
        if (isset($reqs['requireNumbers']) && $reqs['requireNumbers'] && !preg_match('/[0-9]/', $password)) {
            $isValid = false;
            $errors[] = "Debe contener al menos un numero";
        }
        if (isset($reqs['requireSymbols']) && $reqs['requireSymbols'] && !preg_match('/[!@#$%^&*()\-_=+\[\]{}|;:,.<>?]/', $password)) {
            $isValid = false;
            $errors[] = "Debe contener al menos un simbolo";
        }

        return ['valid' => $isValid, 'errors' => $errors];
    }
}
?>