<?
    global $version;
    $version = "0.10";

    // effect_id
    define('DEATH',         '836045448945493');
    define('DAMAGE',        '836045448945501');
    define('HEAL',          '836045448945500');
    define('FIGHT_START',   '836045448945489');
    define('FIGHT_END',     '836045448945490');
    define('REVIVAL',       '812826855735296');
    
    // hit_type_id
    define('MISS',          '836045448945502');
    define('PARRY',         '836045448945503');
    define('DEFLECT',       '836045448945508');
    define('DODGE',         '836045448945505');
    define('COVER',         '836045448945510');
    define('IMMUNE',        '836045448945506');
    define('RESIST',        '836045448945507');
    
    // classes
    define('KNIGHT',        2 );
    define('WARRIOR',       8 );
        define('GUARDIAN',      11);
        define('JUGGERNAUT',    23);
        define('SENTINEL',      12);
        define('MARAUDER',      24);
        
    define('CONSULAR',      1 );
    define('INQUISITOR',    7 );
        define('SAGE',          9 );
        define('SORCERER',      22);
        define('SHADOW',        10);
        define('ASSASSIN',      21);
    
    define('TROOPER',       4 );
    define('BOUNTYHUNTER',  5 );
        define('COMMANDO',      15);
        define('MERCENARY',     17);
        define('VANGUARD',      16);
        define('POWERTECH',     18);
    
    define('SMUGGLER',      3 );
    define('IMPERIALAGENT', 6 );
        define('GUNSLINGER',    13);
        define('SNIPER',        20);
        define('SCOUNDREL',     14);
        define('OPERATIVE',     19);
?>