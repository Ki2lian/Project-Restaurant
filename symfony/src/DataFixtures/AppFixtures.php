<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public $namePrefix = [
        'Belly','Big','Blue Plate','Fast','Fat','Golden','Hungry','Salty',
        'Silver','Smokestack','Spice','Sugar','Sweet','Thirsty','Red','Blue','Green','Orange'
    ];

    public $nameSuffix = [
        'Bakery','Bar & Grill','BBQ','Box','Brasserie','Burger',
        'Creamery','Curry','Deli','Diner','Dragon','Eatery',
        'Eats','Gastropub','Grill','Grill & Tap','House','Juice Bar',
        'King','Kitchen','Pizza','Pub','Shakes','Spoon','Steakhouse','Subs'
    ];

    public $foodNames = [
        'Cheese Pizza', 'Hamburger', 'Cheeseburger', 'Bacon Burger', 'Bacon Cheeseburger',
        'Little Hamburger', 'Little Cheeseburger', 'Little Bacon Burger', 'Little Bacon Cheeseburger',
        'Veggie Sandwich', 'Cheese Veggie Sandwich', 'Grilled Cheese',
        'Cheese Dog', 'Bacon Dog', 'Bacon Cheese Dog', 'Pasta'
    ];

    public $beverageNames = [
        'Beer', 'Bud Light', 'Budweiser', 'Miller Lite',
        'Milk Shake', 'Tea', 'Sweet Tea', 'Coffee', 'Hot Tea',
        'Champagne', 'Wine', 'Lemonade', 'Coca-Cola', 'Diet Coke',
        'Water', 'Sprite', 'Orange Juice', 'Iced Coffee'
    ];

    public $dairyNames = [
        'Butter',
        'Egg',
        'Cheese',
        'Sour cream',
        'Mozzarella',
        'Yogurt',
        'Cream',
        'Milk',
        'Custard',
    ];

    public $vegetableNames = [
        'Onion',
        'Garlic',
        'Tomato',
        'Potato',
        'Carrot',
        'Bell Pepper',
        'Bell Basil',
        'Parsley',
        'Broccoli',
        'Corn',
        'Spinach',
        'Ginger',
        'Chili',
        'Celery',
        'Rosemary',
        'Cucumber',
        'Pickle',
        'Avocado',
        'Pumpkin',
        'Mint',
        'Eggplant',
        'Yam',
    ];

    public $fruitNames = [
        'Lemon',
        'Apple',
        'Banana',
        'Lime',
        'Strawberry',
        'Orange',
        'Pineapple',
        'Blueberry',
        'Raisin',
        'Coconut',
        'Grape',
        'Peach',
        'Raspberry',
        'Cranberry',
        'Mango',
        'Pear',
        'Blackberry',
        'Cherry',
        'Watermelon',
        'Kiwi',
        'Papaya',
        'Guava',
        'Lychee',
    ];

    public $meatNames = [
        'Chicken',
        'Bacon',
        'Sausage',
        'Beef',
        'Ham',
        'Hot dog',
        'Pork',
        'Turkey',
        'Chicken wing',
        'Chicken breast',
        'Lamb',
    ];

    public $sauceNames = [
        'Tomato sauce',
        'Tomato paste',
        'Mayonnaise sauce',
        'BBQ sauce',
        'Chili sauce',
        'Garlic sauce',
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        
        for ($i=0; $i < 40; $i++) {
            $user = new User();
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $user   ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setEmail("$firstName.$lastName@gmail.com")
                    ->setIsVerified(true)
                    ->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $user,
                            "$firstName-SaFetyAb0veALl"
                        )
                    )
            ;
            $isRestaurateur = random_int(0,1);
            if($isRestaurateur) $user->setRoles(["ROLE_ADMIN"]);
            $manager->persist($user);
            
            $haveRestaurant = random_int(0,1);
            if($isRestaurateur){
                if($haveRestaurant){
                    $restaurant = new Restaurant();
                    $restaurant->setName(
                                    $faker->randomElement($this->namePrefix)." ".
                                    $faker->randomElement($this->nameSuffix)
                                )
                                ->setAddress($faker->address())
                                ->setPhone($faker->phoneNumber())
                                ->addResponsable($user)
                    ;
                    $manager->persist($restaurant);

                    $howManyFoods = random_int(2, 7);
                    $temp = $this->foodNames;
                    for ($productFood=0; $productFood < $howManyFoods; $productFood++) {
                        $product = new Product();
                        $foodNames = $faker->randomElement($temp);
                        $pos = array_search($foodNames, $temp);
                        array_splice($temp, $pos, 1);

                        $product->setName($foodNames)
                                ->setPrice($faker->randomFloat(2, 5, 30))
                                ->setDescription(
                                    $faker->randomElement($this->meatNames).", ".
                                    $faker->randomElement($this->sauceNames).", ".
                                    $faker->randomElement($this->dairyNames).", ".
                                    $faker->randomElement($this->vegetableNames)." and ".
                                    $faker->randomElement($this->vegetableNames)
                                )
                                ->setRestaurant($restaurant)
                        ;
                        $manager->persist($product);
                    }

                    $howManyBeverage = random_int(2, 5);
                    $temp = $this->beverageNames;
                    for ($productBeverage=0; $productBeverage < $howManyBeverage; $productBeverage++) { 
                        $beverage = new Product();
                        $beverageNames = $faker->randomElement($temp);
                        $pos = array_search($beverageNames, $temp);
                        array_splice($temp, $pos, 1);

                        $beverage->setName($beverageNames)
                                ->setPrice($faker->randomFloat(2, 2, 10))
                                ->setDescription('')
                                ->setRestaurant($restaurant)
                        ;
                        $manager->persist($beverage);
                    }
                }
            }
        }
        
        $manager->flush();
    }
}
