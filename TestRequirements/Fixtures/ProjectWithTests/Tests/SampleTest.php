<?php

namespace Tests\SampleTest;

use ProjectWithTests\SampleFile;
use Tests\Helper;

test(
    title: 'this is not going to run',
    case: function () {
        SampleFile\anImportantFunction();
    },
);
