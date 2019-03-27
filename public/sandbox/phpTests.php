<?php
$myArray = array("foo", "bar", "hello", "world");
for($i = 0; $i < 2; $i++) {
    echo "Element $myArray[$i] is to be deleted.\n";
    array_splice($myArray, 0, 1);
}
print_r($myArray);
?>