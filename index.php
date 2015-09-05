<?php
    require_once('classes/dbconnection.php');

    $mongo = DBConnection::singleton();
    $articleCollection = $mongo->getCollection('mongo_blog.sample_articles');


    $currentPage = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
    $articlesPerPage = 5;
    $skip = ($currentPage - 1) * $articlesPerPage;

    $cursor = $articleCollection->find();
    
    $totalArticles = $cursor->count();
    $totalPages = (int) ceil($totalArticles / $articlesPerPage);
    
    $cursor->sort(array('published_at' => -1))->skip($skip)->limit($articlesPerPage);
    
    $latestArticle = $mongo->getLatestArticles('mongo_blog.sample_articles', 3);
    
    // get the list of categories
    $db = $mongo->database;
    
    // Find the number of articles per category
    // define the map function
    $map = new MongoCode("function(){ emit(this.category, 1); }");
    
    // define the reduce function
    $reduce = new MongoCode("function(key, values){"
                . "count = 0; "
                . "for (var i = 0; i < values.length; i++){"
                    . "count += values[i];"
                . "}"
                . "return count;"
            . "}");
    $command = array(
        'mapreduce'     => 'mongo_blog.sample_articles',
        'map'           => $map,
        'reduce'        => $reduce,
        'out'           => 'articles_per_category'
    );
    
    $db->command($command);
    
    // load all the categories in an array
    $crsr = $db->selectCollection('articles_per_category')
                                       ->find();
    $categories = iterator_to_array($crsr);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>All the articles</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="" />
        <meta name="author" content="Malek Beloula" />
        <!-- css -->
        <link href="static/css/bootstrap.min.css" rel="stylesheet" />
        <link href="static/css/fancybox/jquery.fancybox.css" rel="stylesheet">
        <link href="static/css/jcarousel.css" rel="stylesheet" />
        <link href="static/css/flexslider.css" rel="stylesheet" />
        <link href="static/css/style.css" rel="stylesheet" />


        <!-- Theme skin -->
        <link href="static/skins/default.css" rel="stylesheet" />

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
              <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
            <![endif]-->

    </head>
    <body>
        <div id="wrapper">
            <!-- start header -->
            <header>
                <div class="navbar navbar-default navbar-static-top">
                    <div class="container">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="index.php"><span>M</span>y <span>M</span>ongo <span>DB</span> <span>B</span>log</a>
                        </div>
                        <div class="navbar-collapse collapse ">
                            <ul class="nav navbar-nav">
                                <li class="active"><a href="index.php">Home</a></li>
                                <li class="dropdown ">
                                    <a href="#" class="dropdown-toggle " data-toggle="dropdown" data-hover="dropdown" data-delay="0" data-close-others="false">Categories <b class=" icon-angle-down"></b></a>
                                    <ul class="dropdown-menu">
                                        <?php foreach($categories as $category): ?>
                                        <li><a href="category.php?category=<?php echo $category['_id']; ?>"><?php echo $category['_id']; ?></a></li>
                                        <?php endforeach;; ?>
                                    </ul>
                                </li>
                                <li><a href="">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            <!-- end header -->
            <section id="inner-headline">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="breadcrumb">
                                <li><a href="#"><i class="fa fa-home"></i></a><i class="icon-angle-right"></i></li>
                                <li class="active">Blog</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            <section id="content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php
                            while ($cursor->hasNext()):
                                $article = $cursor->getNext();
                                ?>
                                <article>
                                    <div class="post-image">
                                        <div class="post-heading">
                                            <h3><a href="blogpost.php?id=<?php echo $article['_id'];?>"><?php echo $article['title']; ?></a></h3>
                                        </div>
                                        <img src="static/img/img1.jpg" alt="" />
                                    </div>
                                    <p>
                                        <?php echo substr($article['description'], 0, 200); ?> 
                                    </p>
                                    <div class="bottom-article">
                                        <ul class="meta-post">
                                            <li><i class="icon-calendar"></i><a href="#"> <?php echo date('M d, Y', $article['published_at']->sec); ?></a></li>
                                            <li><i class="icon-user"></i><a href="#"> Admin</a></li>
                                            <li><i class="icon-folder-open"></i><a href="#"> Blog</a></li>
                                            <li><i class="icon-comments"></i><a href="#">4 Comments</a></li>
                                        </ul>
                                        <a href="blogpost.php?id=<?php echo $article['_id'];?>" class="pull-right">Continue reading <i class="icon-angle-right"></i></a>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                            <div id="pagination">
                                <span class="all"><?php echo "Page $currentPage of $totalPages"?></span>
                                <?php for ($i = 1; $i <= $totalPages; $i++){ 
                                    if($i === $currentPage){?>                                        
                                        <span class="current"><?php echo $currentPage; ?></span>
                                    <?php } else { ?>
                                        <a href="index.php?page=<?php echo $i; ?>" class="inactive"><?php echo $i; ?></a>                                        
                                   <?php }
                                   } ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <aside class="right-sidebar">
                                <div class="widget">
                                    <form class="form-search">
                                        <input class="form-control" type="text" placeholder="Search..">
                                    </form>
                                </div>
                                <div class="widget">
                                    <h5 class="widgetheading">Categories</h5>
                                    <ul class="cat">
                                        <?php foreach($categories as $category): ?>
                                            <li><i class="icon-angle-right"></i><a href="category.php?category=<?php echo $category['_id']; ?>"><?php echo $category['_id']; ?></a><span> <?php echo $category['value']; ?></span></li>                                        
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="widget">
                                    <h5 class="widgetheading">Latest posts</h5>
                                    <ul class="recent">
                                        <?php
                                        while ($latestArticle->hasNext()):
                                            $article = $latestArticle->getNext();
                                            ?>
                                            <li>
                                                <img src="static/img/thumb1.jpg" class="pull-left" alt="" />
                                                <h6><a href="blogpost.php?id=<?php echo $article['_id'];?>"><?php echo substr($article['title'], 0, 20); ?></a></h6>
                                                <p>
                                                    <?php echo substr($article['description'], 0, 60) ?>
                                                </p>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                </div>
                                <div class="widget">
                                    <h5 class="widgetheading">Popular tags</h5>
                                    <ul class="tags">
                                        <li><a href="#">Web design</a></li>
                                        <li><a href="#">Trends</a></li>
                                        <li><a href="#">Technology</a></li>
                                        <li><a href="#">Internet</a></li>
                                        <li><a href="#">Tutorial</a></li>
                                        <li><a href="#">Development</a></li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </section>
            <footer>
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="widget">
                                <h5 class="widgetheading">Get in touch with us</h5>
                                <address>
                                    <strong>Malek Beloula</strong><br>
                                    Batna<br>
                                    Algeria</address>
                                <p>
                                    <i class="icon-phone"></i> (00213) 699-853-734<br>
                                    <i class="icon-envelope-alt"></i> abdelmalek.beloula@gmail.com
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="widget">
                                <h5 class="widgetheading">Pages</h5>
                                <ul class="link-list">
                                    <li><a href="#">Press release</a></li>
                                    <li><a href="#">Terms and conditions</a></li>
                                    <li><a href="#">Privacy policy</a></li>
                                    <li><a href="#">Career center</a></li>
                                    <li><a href="#">Contact us</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="widget">
                                <h5 class="widgetheading">Latest posts</h5>
                                <ul class="link-list">
                                    <li><a href="#">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</a></li>
                                    <li><a href="#">Pellentesque et pulvinar enim. Quisque at tempor ligula</a></li>
                                    <li><a href="#">Natus error sit voluptatem accusantium doloremque</a></li>
                                </ul>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div id="sub-footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="copyright">
                                    <p>
                                        <span>&copy; Malek Beloula 2015 All right reserved. By </span><a href="" target="_blank">Bootstraptaste</a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <ul class="social-network">
                                    <li><a href="#" data-placement="top" title="Facebook"><i class="fa fa-facebook"></i></a></li>
                                    <li><a href="#" data-placement="top" title="Twitter"><i class="fa fa-twitter"></i></a></li>
                                    <li><a href="#" data-placement="top" title="Linkedin"><i class="fa fa-linkedin"></i></a></li>
                                    <li><a href="#" data-placement="top" title="Pinterest"><i class="fa fa-pinterest"></i></a></li>
                                    <li><a href="#" data-placement="top" title="Google plus"><i class="fa fa-google-plus"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <a href="#" class="scrollup"><i class="fa fa-angle-up active"></i></a>
        <!-- javascript
            ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="static/js/jquery.js"></script>
        <script src="static/js/jquery.easing.1.3.js"></script>
        <script src="static/js/bootstrap.min.js"></script>
        <script src="static/js/jquery.fancybox.pack.js"></script>
        <script src="static/js/jquery.fancybox-media.js"></script>
        <script src="static/js/google-code-prettify/prettify.js"></script>
        <script src="static/js/portfolio/jquery.quicksand.js"></script>
        <script src="static/js/portfolio/setting.js"></script>
        <script src="static/js/jquery.flexslider.js"></script>
        <script src="static/js/animate.js"></script>
        <script src="static/js/custom.js"></script>
    </body>
</html>