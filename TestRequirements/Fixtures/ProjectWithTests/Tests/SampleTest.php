<?php

namespace Tests\SampleTest;

use ProjectWithTests\SampleFile;

test(
    title: 'this is not going to run',
    case: function () {
        SampleFile\anImportantFunction();
    },
);
