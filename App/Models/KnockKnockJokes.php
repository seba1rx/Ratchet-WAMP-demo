<?php

namespace App\Models;

class KnockKnockJokes
{

    private static $jokes = [
        [

            "Leaf",
            "Leaf me alone!",
        ],
        [
            "Radio",
            "Radio not, here I come! "
        ],
        [
            "Tank",
            "You're welcome!"
        ],
        [
            "Orange",
            "Orange you going to let me in?"
        ],
        [
            "Nana",
            "Nana your business!"
        ],
        [
            "Lettuce",
            "Lettuce in! "
        ],
        [
            "Boo",
            "Don't cry, it's just a joke!"
        ],
        [
            "Justin",
            "Justin time! "
        ],
        [
            "Candice",
            "Candice joke get any worse?"
        ],
        [
            "Theodore",
            "Theodore wasn't opened, so I knocked"
        ],
        [
            "Alpaca",
            "Alpaca the suitcase if you pack the car!"
        ],
        [
            "Nobel",
            "There's nobel…that's why I knocked"
        ],
        [
            "Luke",
            "Luke through the peephole and find out!"
        ],
        [
            "Figs",
            "Figs the doorbell - it's not working!"
        ],


        [
            "Weirdo",
            "Weirdo you think you're going?"
        ],

        [
            "Dishes",
            "Dishes the police, open up!"
        ],

        [
            "Annie",
            "Annie thing you can do, I can do, too!"
        ],

        [
            "Hal",
            "Hal will you know if you don't open the door?"
        ],

        [
            "Says",
            "Says me!"
        ],

        [

            "Anita",
            "Anita use the bathroom, so open up!"
        ],

        [
            "Ice cream",
            "Ice cream so you can hear me!"
        ],

        [
            "Honey bee",
            "Honey bee a dear and get that door!"
        ],

        [
            "A little old lady",
            "Hey, you can yodel!"
        ],

        [
            "Canoe",
            "Canoe come open the door?"
        ],

        [
            "Needle",
            "Needle little help here opening the door!"
        ],

        [
            "Snow",
            "Snow use, the joke is over"
        ],

        [
            "Hawaii",
            "I'm good, Hawaii you?"
        ],

        [
            "Woo",
            "Glad you're excited to see me!"
        ],

        [
            "Iran",
            "Iran here. I'm tired"
        ],

        [
            "To",
            "Actually, it's to whom"
        ],

        [
            "Amarillo",
            "Amarillo nice person"
        ],

        [
            "Etch",
            "Bless you!",
        ],

        [
            "Cher",
            "Cher would be nice if you opened the door!"
        ],

        [
            "Stopwatch",
            "Stopwatch you're doing and open the door!"
        ],

        [
            "Icy",
            "Icy you looking at me!"
        ],

        [
            "Dozen",
            "Dozen anyone want to let me in?"
        ],

        [
            "Cash",
            "No thanks, but I'd love some almonds!"
        ],

        [
            "Voodoo",
            "Voodoo you think you are?"
        ],

        [
            "Mary",
            "Merry Christmas!"
        ],

        [
            "Ketchup",
            "Ketchup with you later!"
        ],

        [
            "Watson",
            "Watson some TV right now"
        ],

        [
            "Yukon",
            "Yukon say that again!"
        ],

        [
            "Ears",
            "Ears another joke for ya!"
        ],

        [
            "Honeydew",
            "Honey do you want to dance?"
        ],

        [
            "Arfur",
            "Arfur got!"
        ],

        [
            "Norma Lee",
            "Norma Lee I don't knock on doors!"
        ],

        [
            "Viper",
            "Viper nose, it's running!"
        ],

        [
            "A herd",
            "A herd you when you got home!"
        ],

        [
            "Claire",
            "Claire a path, I'm coming through!"
        ],

        [
            "Cook",
            "Who you callin' coocoo?"
        ],

        [
            "Howl",
            "Howl you know unless you open up?"
        ],

        [
            "Ya",
            "Yappee!"
        ],

        [
            "Harry",
            "Harry up, it's cold out here!"
        ],

        [
            "Ivor",
            "Ivor you let me in or I'm leaving!"
        ],

        [
            "Sadie",
            "Sadie magic word and I'll come in!"
        ],

        [
            "Two knee",
            "Two-knee fish!"
        ],

        [
            "Abby",
            "Abby birthday to you!"
        ],

        [
            "Adore",
            "Adore is between us, open up!"
        ],

        [
            "Scold",
            "It's scold outside, let me in!"
        ],

        [
            "Cargo",
            "Car go, Vroooom"
        ],

        [
            "Annie",
            "Annie-body there?"
        ],

        [
            "Owls go",
            "That's right!"
        ],

        [
            "Ben",
            "Ben knocking for 10 minutes, open up!"
        ],

        [
            "Armageddon",
            "Armageddon cold, open up!"
        ],

        [
            "Leon",
            "Leon me when you're not strong!"
        ],

        [
            "Lena",
            "Lena little closer and I'll tell you!"
        ],

        [
            "Tiss",
            "A tiss-who is for blowing your nose!"
        ],

        [
            "Hike",
            "A Haiku is a Japanese poem!"
        ],

        [
            "Quiche",
            "Quiche me!"
        ],

        [
            "Juno",
            "Juno how funny this is?"
        ],

        [
            "Weekend",
            "Weekend do anything you want!"
        ],

        [
            "Kanga",
            "Actually, it's kangaroo"
        ],

        [
            "Ruff",
            "Who let the dogs out?"
        ],

        [
            "Hatch",
            "God bless you!"
        ],

        [
            "Kenya",
            "Kenya feel the love tonight?",
        ],

        [
            "Mustache",
            "I mustache you a question…",
        ],

        [
            "Butter",
            "Butter open the door!",
        ],

        [
            "Bacon",
            "Bacon some cookies, they smell delicious!",
        ],

        [
            "Gouda",
            "Gouda knock jokes, don't you think?",
        ],

        [
            "Goliath",
            "Goliath down if you're tired!",
        ],

        [
            "Wooden shoe",
            "Wooden shoe like to hear another joke?",
        ],

        [
            "Doris",
            "Doris locked, that's why I'm knocking!",
        ],

        [
            "Noah",
            "Noah anyone who can open this door?",
        ],

        [
            "Hope",
            "Hope you can open this door!"
        ],

        [
            "Wood",
            "Wood you like to hear another joke?"
        ],

        [
            "Pew",
            "Pew. Pew. Pew"
        ],

        [
            "I.O",
            "You owe ME. Can you pay me back?"
        ],

        [
            "Barbie",
            "No, Barbie CUE"
        ],

        [
            "Tatt",
            "No thanks, I'm too young for a tattoo!"
        ],

        [
            "Scooby",
            "Scooby Doo of course!"
        ],

        [
            "Razor",
            "Razor your hand if you have a question!"
        ],

        [
            "Yah",
            "Nah, I prefer Google"
        ],

        [
            "Tritan",
            "Tritan tell you a joke!"
        ],

        [
            "Jess",
            "Jess me, myself and I!"
        ],

        [
            "Turnip",
            "Turnip the volume, I love this song!",
        ],

        [
            "Broken pencil",
            "Never mind, there's no point!",
        ],

        [
            "Howard",
            "Howard I know?",
        ],

        [
            "Banana",
            "Banana split!",
        ],

        [
            "Pecan",
            "Hey! Pecan someone your own size!",
        ],

    ];

    public static function getRandomJoke(){
        $rand_joke_key = array_rand(self::$jokes);
        $rand_joke = self::$jokes[$rand_joke_key];
        $setup = $rand_joke[0];
        $punchline = $rand_joke[1];
        $joke = [
            "Knock, knock",
            "Who's there?",
            $setup,
            $setup . " who?",
            $punchline
        ];
        return ["joke_id" => $rand_joke_key, "joke" => $joke];
    }

    public static function getJokeById(int $id): array
    {
        $rand_joke = self::$jokes[$id];
        $setup = $rand_joke[0];
        $punchline = $rand_joke[1];
        $joke = [
            "Knock, knock",
            "Who's there?",
            $setup,
            $setup . " who?",
            $punchline
        ];
        return ["joke_id" => $id, "joke" => $joke];
    }

    public static function getAllJokes(): array
    {
        $all_the_jokes = [];
        foreach(self::$jokes as $joke_id => $joke){

            $setup = $joke[0];
            $punchline = $joke[1];
            $the_joke = [
                "Knock, knock",
                "Who's there?",
                $setup,
                $setup . " who?",
                $punchline
            ];

            $all_the_jokes[] = ["joke_id" => $joke_id, "joke" => $the_joke];
        }

        return $all_the_jokes;
    }
}
