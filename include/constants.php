<?
    global $version;
    $version = "0.12";

    // effect_id
    define('DEATH',         '836045448945493');
    define('DAMAGE',        '836045448945501');
    define('HEAL',          '836045448945500');
    define('FIGHT_START',   '836045448945489');
    define('FIGHT_END',     '836045448945490');
    define('REVIVAL',       '812826855735296');
    // used whith effect_type SPEND, RESTORE
    define('FORCE',         '836045448938502');
    define('HITPOINT',      '836045448938504');
    define('ENERGY',        '836045448938503');
    define('RAGE',          '836045448938497');
    define('AMMO',          '836045448938498');
    define('FOCUS',         '836045448938496');
    define('HEAT',          '836045448938499');
    
    
    // effect_type_id
    define('SPEND',         '836045448945473');
    define('RESTORE',       '836045448945476');
    
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

    // bosses
    // Eternity Vault
    $boss['XRR-3']  = 2034573252755456;
    $boss['XRR-3']  = 1779997656219648;
    $boss['Gharj']  = 2034526008115200;
    $boss['Gharj']  = 1783772932472832;
    $boss['Soa'][]  = 2289823159156736;
    $boss['Soa'][]  = 1783790112342016;
    // Denova
    $boss['Zorn'][] = 2857544821243904;
    $boss['Zorn'][] = 2788331423268864;
    $boss['Zorn'][] = 2860770341683200;
    $boss['Zorn'][] = 2861388816973824;
    $boss['Toth'][] = 2857549116211200;
    $boss['Toth'][] = 2788335718236160;
    $boss['Toth'][] = 2860766046715904;
    $boss['Toth'][] = 2861384522006528;
?>