function uhash($string) {
    // ตรวจสอบว่าสตริงที่ให้มาไม่เป็นค่าว่าง
    if (empty($string)) {
        return false;
    }

    // กำหนดตัวแปรที่เก็บผลลัพธ์เริ่มต้น
    $result = '';

    // วนลูปตามจำนวนตัวอักษรในสตริง
    for ($i = 0; $i < strlen($string); $i++) {
        // หาค่า ASCII ของแต่ละตัวอักษร
        $ascii = ord($string[$i]);

        // ทำการเพิ่มค่า ASCII และนำมา modulo 26 เพื่อให้ได้ตัวอักษร A-Z
        $newAscii = ($ascii + $i) % 26;

        // แปลงค่า ASCII เป็นตัวอักษร
        $newChar = chr($newAscii + 65);

        // เก็บตัวอักษรใหม่ลงในผลลัพธ์
        $result .= $newChar;
    }

    // สลับตำแหน่งตัวอักษรในผลลัพธ์
    $result = strrev($result);

    // คืนค่าผลลัพธ์ที่มีความยาว 9 ตัวอักษร
    return substr($result, 0, 9);
        }
