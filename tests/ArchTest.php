<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'ad'])
    ->each->not->toBeUsed();
