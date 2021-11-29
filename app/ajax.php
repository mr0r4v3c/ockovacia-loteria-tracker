<?php
namespace SimonOravec\Loteria;
require(__DIR__.'/classes/loteria.class.php');
$app = new Loteria();

header("Content-type: application/json");

/**
 * @param boolean $success
 * @param string $error
 * @param mixed $data
 * 
 * @return string
 */
function generateOutput($success, $error, $data) 
{
    return json_encode([
        'success'=>$success,
        'error'=>$error,
        'data'=>$data
    ]);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') 
{
    die(generateOutput(false, 'Neplatný požiadavok', null));
}

if (isset($_POST['action']))
{
    switch ($_POST['action']) 
    {
        case 'check_codes':
            $app->loadDataFromMFSR();
            $codes = $app->checkCodes();

            $out = '';

            foreach(array_keys($codes) as $code) 
            {
                if ($codes[$code]['win'])
                {
                    $data = $codes[$code]['data'];
                    $out .= "<tr id='cc_{$code}'><td>{$code}</td><td class='win'>ÁNO</td><td>{$data['meno']}</td><td>{$data['obec']}</td><td>{$data['vyherna suma']} €</td><td class='text-center'><a class='delete-btn' onclick=\"deleteCode('{$code}')\">&#10060;</a></td></tr>";
                }
                else
                {
                    $out .= "<tr id='cc_{$code}'><td>{$code}</td><td class='not-win'>NIE</td><td>-</td><td>-</td><td>-</td><td class='text-center'><a class='delete-btn' onclick=\"deleteCode('{$code}')\">&#10060;</a></td></tr>";
                }
            }

            $out = base64_encode($out);

            die(generateOutput(true, null, $out));
            break;

        case 'add_code':
            $code = $_POST['code'];

            if (empty($code) || !$app->validateCode($code)) {
                die(generateOutput(false, 'Neplatný kód', null));
            }

            if ($app->addCode($code)) {
                $app->loadDataFromMFSR();
                $data = $app->getCodeData($code);
                if ($data == null) {
                    $out = "<tr id='cc_{$code}'><td>{$code}</td><td class='not-win'>NIE</td><td>-</td><td>-</td><td>-</td><td class='text-center'><a class='delete-btn' onclick=\"deleteCode('{$code}')\">&#10060;</a></td></tr>";
                } else {
                    $out = "<tr id='cc_{$code}'><td>{$code}</td><td class='win'>ÁNO</td><td>{$data['meno']}</td><td>{$data['obec']}</td><td>{$data['vyherna suma']} €</td><td class='text-center'><a class='delete-btn' onclick=\"deleteCode('{$code}')\">&#10060;</a></td></tr>";
                }
                $out = base64_encode($out);

                die(generateOutput(true, null, $out));
            } else {
                die(generateOutput(false, 'Tento kód už je pridaný', null));
            }
            break;

        case 'delete_code':
            if ($app->removeCode($_POST['code'])) {
                die(generateOutput(true, null, null));
            } else {
                die(generateOutput(false, 'Neplatný kód', null));
            }
            break;

        default:
            die(generateOutput(false, 'Neplatný požiadavok', null));
    }
} 
else 
{
    die(generateOutput(false, 'Neplatný požiadavok', null));
}
?>