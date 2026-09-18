<?php

namespace KikCMS\Domain\FrontendForm\SpamBlock;

use Symfony\Component\Validator\Constraint;

class SpamBlock extends Constraint
{
    public string $message = 'spamblock';

    const string SESSION_KEY = 'spamblock_question_id';

    const string COLOR_RED    = '🔴';
    const string COLOR_ORANGE = '🟠';
    const string COLOR_YELLOW = '🟡';
    const string COLOR_GREEN  = '🟢';
    const string COLOR_BLUE   = '🔵';
    const string COLOR_PURPLE = '🟣';
    const string COLOR_BROWN  = '🟤';
    const string COLOR_BLACK  = '⚫';
    const string COLOR_WHITE  = '⚪';

    const string COLOR_ID_RED    = 'red';
    const string COLOR_ID_ORANGE = 'orange';
    const string COLOR_ID_YELLOW = 'yellow';
    const string COLOR_ID_GREEN  = 'green';
    const string COLOR_ID_BLUE   = 'blue';
    const string COLOR_ID_PURPLE = 'purple';
    const string COLOR_ID_BROWN  = 'brown';
    const string COLOR_ID_BLACK  = 'black';
    const string COLOR_ID_WHITE  = 'white';

    const array COLOR_MAP = [
        self::COLOR_ID_RED    => self::COLOR_RED,
        self::COLOR_ID_ORANGE => self::COLOR_ORANGE,
        self::COLOR_ID_YELLOW => self::COLOR_YELLOW,
        self::COLOR_ID_GREEN  => self::COLOR_GREEN,
        self::COLOR_ID_BLUE   => self::COLOR_BLUE,
        self::COLOR_ID_PURPLE => self::COLOR_PURPLE,
        self::COLOR_ID_BROWN  => self::COLOR_BROWN,
        self::COLOR_ID_BLACK  => self::COLOR_BLACK,
        self::COLOR_ID_WHITE  => self::COLOR_WHITE,
    ];

    const array QUESTIONS = [
        1  => self::COLOR_ID_BLUE,
        2  => self::COLOR_ID_BLUE,
        3  => self::COLOR_ID_GREEN,
        4  => self::COLOR_ID_GREEN,
        5  => self::COLOR_ID_GREEN,
        6  => self::COLOR_ID_GREEN,
        7  => self::COLOR_ID_GREEN,
        8  => self::COLOR_ID_YELLOW,
        9  => self::COLOR_ID_YELLOW,
        10 => self::COLOR_ID_YELLOW,
        11 => self::COLOR_ID_YELLOW,
        12 => self::COLOR_ID_ORANGE,
        13 => self::COLOR_ID_ORANGE,
        14 => self::COLOR_ID_RED,
        15 => self::COLOR_ID_RED,
        16 => self::COLOR_ID_RED,
        17 => self::COLOR_ID_RED,
        18 => self::COLOR_ID_RED,
        19 => self::COLOR_ID_RED,
        20 => self::COLOR_ID_RED,
        21 => self::COLOR_ID_WHITE,
        22 => self::COLOR_ID_WHITE,
        23 => self::COLOR_ID_BROWN,
        24 => self::COLOR_ID_BROWN,
        25 => self::COLOR_ID_BROWN,
        26 => self::COLOR_ID_BROWN,
        27 => self::COLOR_ID_BROWN,
        28 => self::COLOR_ID_PURPLE,
        29 => self::COLOR_ID_PURPLE,
        30 => self::COLOR_ID_BLACK,
    ];
}