#!/bin/bash

composer2 test

c2exit=$?

test -f /app/source/log/oxideshop.log && cat /app/source/log/oxideshop.log

exit $c2exit
