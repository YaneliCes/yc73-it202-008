<?php
$a1 = [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10];
$a2 = [-1, 1, -2, 2, 3, -3, -4, 5];
$a3 = [-0.01, -0.0001, -.15];
$a4 = ["-1", "2", "-3", "4", "-5", "5", "-6", "6", "-7", "7"];

function bePositive($arr) {
    echo "<br>Processing Array:<br><pre>" . var_export($arr, true) . "</pre>";
    echo "<br>Positive output:<br>";
    //note: use the $arr variable, don't directly touch $a1-$a4
    //TODO use echo to output all of the values as positive (even if they were originally positive) and maintain the original datatype
    //hint: may want to use var_dump() or similar to show final data types

    //yc73
    //2-1/24
    foreach ($arr as $num) {
        //got help from: https://www.w3schools.com/php/php_numbers.asp
        if (is_int($num)) {
            //got help from:https://www.w3schools.com/php/php_casting.asp
            $num = (int)abs($num);
            echo $num . "<br>";
        }
        elseif (is_float($num)) {
            $num = (float)abs($num);
            echo $num . "<br>";
        }
        elseif (is_string($num)) {
            $num = (string)abs($num);
            echo $num . "<br>";
        }
    }
}
echo "Problem 3: Be Positive<br>";
?>
<table>
    <thread>
        <th>A1</th>
        <th>A2</th>
        <th>A3</th>
        <th>A4</th>
    </thread>
    <tbody>
        <tr>
            <td>
                <?php bePositive($a1); ?>
            </td>
            <td>
                <?php bePositive($a2); ?>
            </td>
            <td>
                <?php bePositive($a3); ?>
            </td>
            <td>
                <?php bePositive($a4); ?>
            </td>
        </tr>
</table>
<style>
    table {
        border-spacing: 2em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
    }
</style>