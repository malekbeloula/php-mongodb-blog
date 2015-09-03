<?php
/***
 * @author: Malek Beloula
 * @description:
 */
require('../classes/dbconnection.php');
// sample of 5 titles
$titles = array(
    'Life is a journey not a destination',
    'Adding manpower to a late software project makes it later.',
    'Research supports a specific theory depending on the amount of funds dedicated to it.',
    'It doesn\'t matter how often but how you are doing it.',
    'Software bugs are hard to detect by anybody except may be the end user.',);

// Sample of 8 authors
$authors = array('Luke Skywalker', 
                'Leia Organa', 
                'Han Solo', 
                'Darth Vader', 
                'Spock', 
                'James Kirk', 
                'Hikaru Sulu', 
                'Nyota Uhura');

// Sample of description generated by 'Lorem ipsum'
$description = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
        . "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";

// Sample of 6 categories
$categories = array('Electronics', 
                    'Mathematics', 
                    'Programming', 
                    'Data Structures', 
                    'Algorithms', 
                    'Computer Networking');

// Sample of 12 tags 
$tags = array('programming', 
              'testing', 
              'webdesign', 
              'tutorial', 
              'howto', 
              'version-control', 
              'nosql', 
              'algorithms',
              'engineering',
              'software',
              'hardware',
              'security');
/**
 * @description generate a random item from an array
 * @param type $array
 * @return type $array
 */
function getRandomArrayItem($array) {
    $length = count($array);
    $randomIndex = mt_rand(0, $length - 1);
    return $array[$randomIndex];
}

/**
 * @description generate a random timestamp for the generated article in a range of last week
 * @return type
 */
function getRandomTimestamp() {
    $randomDigit = mt_rand(0, 6) * -1;
    return strtotime($randomDigit . ' day');
}
/**
 * @description Generate an article from the arrays (authors, categories, tags, description and a generated timestamp)
 * @global array $titles
 * @global array $authors
 * @global array $categories
 * @global array $tags
 * @return type $array
 */
function createDoc() {
    global $titles, $authors, $categories, $tags, $description;
    $title = getRandomArrayItem($titles);
    $author = getRandomArrayItem($authors);
    $category = getRandomArrayItem($categories);
    $articleTags = array();
    // fix the number of tags for an article x from 1 to 5 tags
    // generate a tag and test whether it is in $articleTags and then push it 
    $numOfTags = rand(1, 5);
    for ($j = 0; $j < $numOfTags; $j++) {
        $tag = getRandomArrayItem($tags);
        if (!in_array($tag, $articleTags)) {
            array_push($articleTags, $tag);
        }
    }
    $rating = mt_rand(1, 10);
    $publishedAt = new MongoDate(getRandomTimestamp());
    return array('title' => $title,
        'author' => $author,
        'description' => $description,
        'category' => $category,
        'tags' => $articleTags,
        'rating' => $rating,
        'published_at' => $publishedAt);
}

$mongo = DBConnection::singleton();
$collection = $mongo->getCollection('sample_articles');
echo "Generating sample articles for mongo_project database...<br/>";
echo "The script is gonna generate 1000 articles...<br/>";
for ($i = 0; $i < 30; $i++) {
    $document = createDoc();
    $mongo->create($collection, $document);
}
echo "Finished!";
