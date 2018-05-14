<?php
require_once(__DIR__ . '/../cashaddr.php');

// convert bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a to 1BpEi6DfDAUFd7GtittLSdBeYJvcoaVggu
function test1() {
 return convertFromCash('bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a') === '1BpEi6DfDAUFd7GtittLSdBeYJvcoaVggu';
}

// convert 1BpEi6DfDAUFd7GtittLSdBeYJvcoaVggu to bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a
function test2() {
 return convertToCash('1BpEi6DfDAUFd7GtittLSdBeYJvcoaVggu') === 'bitcoincash:qpm2qsznhks23z7629mms6s4cwef74vcwvy22gdx6a';
}

// convert bitcoincash:qr95sy3j9xwd2ap32xkykttr4cvcu7as4y0qverfuy to 1KXrWXciRDZUpQwQmuM1DbwsKDLYAYsVLR
function test3() {
 return convertFromCash('bitcoincash:qr95sy3j9xwd2ap32xkykttr4cvcu7as4y0qverfuy') === '1KXrWXciRDZUpQwQmuM1DbwsKDLYAYsVLR';
}

// convert 1KXrWXciRDZUpQwQmuM1DbwsKDLYAYsVLR to bitcoincash:qr95sy3j9xwd2ap32xkykttr4cvcu7as4y0qverfuy
function test4() {
 return convertToCash('1KXrWXciRDZUpQwQmuM1DbwsKDLYAYsVLR') === 'bitcoincash:qr95sy3j9xwd2ap32xkykttr4cvcu7as4y0qverfuy';
}

// convert bitcoincash:qqq3728yw0y47sqn6l2na30mcw6zm78dzqre909m2r to 16w1D5WRVKJuZUsSRzdLp9w3YGcgoxDXb
function test5() {
 return convertFromCash('bitcoincash:qqq3728yw0y47sqn6l2na30mcw6zm78dzqre909m2r') === '16w1D5WRVKJuZUsSRzdLp9w3YGcgoxDXb';
}

// convert 16w1D5WRVKJuZUsSRzdLp9w3YGcgoxDXb to bitcoincash:qqq3728yw0y47sqn6l2na30mcw6zm78dzqre909m2r
function test6() {
 return convertToCash('16w1D5WRVKJuZUsSRzdLp9w3YGcgoxDXb') === 'bitcoincash:qqq3728yw0y47sqn6l2na30mcw6zm78dzqre909m2r';
}

// convert 3CWFddi6m4ndiGyKqzYvsFYagqDLPVMTzC to bitcoincash:ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq
function test7() {
 return convertToCash('3CWFddi6m4ndiGyKqzYvsFYagqDLPVMTzC') === 'bitcoincash:ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq';
}

// convert bitcoincash:ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq to 3CWFddi6m4ndiGyKqzYvsFYagqDLPVMTzC
function test8() {
 return convertFromCash('bitcoincash:ppm2qsznhks23z7629mms6s4cwef74vcwvn0h829pq') === '3CWFddi6m4ndiGyKqzYvsFYagqDLPVMTzC';
}

// convert 3LDsS579y7sruadqu11beEJoTjdFiFCdX4 to bitcoincash:pr95sy3j9xwd2ap32xkykttr4cvcu7as4yc93ky28e
function test9() {
 return convertToCash('3LDsS579y7sruadqu11beEJoTjdFiFCdX4') === 'bitcoincash:pr95sy3j9xwd2ap32xkykttr4cvcu7as4yc93ky28e';
}

// convert bitcoincash:pr95sy3j9xwd2ap32xkykttr4cvcu7as4yc93ky28e to 3LDsS579y7sruadqu11beEJoTjdFiFCdX4
function test10() {
 return convertFromCash('bitcoincash:pr95sy3j9xwd2ap32xkykttr4cvcu7as4yc93ky28e') === '3LDsS579y7sruadqu11beEJoTjdFiFCdX4';
}

// convert 31nwvkZwyPdgzjBJZXfDmSWsC4ZLKpYyUw to bitcoincash:pqq3728yw0y47sqn6l2na30mcw6zm78dzq5ucqzc37
function test11() {
 return convertToCash('31nwvkZwyPdgzjBJZXfDmSWsC4ZLKpYyUw') === 'bitcoincash:pqq3728yw0y47sqn6l2na30mcw6zm78dzq5ucqzc37';
}

// convert bitcoincash:pqq3728yw0y47sqn6l2na30mcw6zm78dzq5ucqzc37 to 31nwvkZwyPdgzjBJZXfDmSWsC4ZLKpYyUw
function test12() {
 return convertFromCash('bitcoincash:pqq3728yw0y47sqn6l2na30mcw6zm78dzq5ucqzc37') === '31nwvkZwyPdgzjBJZXfDmSWsC4ZLKpYyUw';
}

runTests(12);

function runTests($n) {
 for ($i = 1; $i <= $n; $i++) {
  try {
   if (call_user_func('test'.$i) === true) {
    echo "Test #$i passed\n";
   } else {
    echo "Test #$i FAILED!\n";
   }
  } catch (Exception $e) {
   echo "==> Got exception from test #$i: ".get_class($e).': '.$e->getMessage()."\n";
  }
 }
}
