<?php

include ('conn.php');

// Check if user is logged in and show toastr
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    $showToastr = true;
    unset($_SESSION['login_success']); // Remove the session variable after displaying toastr
} else {
    $showToastr = false;
}

// Query to fetch announcements
$result = $conn->query("SELECT a.id, u.name AS user, a.title, a.second_title, a.description, a.date_start, a.date_end, a.image FROM announcements a JOIN users u ON a.user_id = u.id ORDER BY a.updated_at DESC");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>COA TSO Special Services Assignment Tracker</title>
  <!-- CSS FILES -->
  <link rel="preconnect" href="https://fonts.googleapis.com">

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-icons.css" rel="stylesheet">

  <link href="css/coa.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

  <!-- notif  -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.4/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<?php 
include ('loader.html');
?>
<?php if ($showToastr): ?>
        <script>
            $(document).ready(function() {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-bottom-right",
                    "timeOut": "3000"
                };
                toastr.success("Login successful! Redirecting to homepage...");
            });
        </script>
    <?php endif; ?>
    <?php
if (isset($_GET['status']) && $_GET['status'] == 'password_changed') {
    echo "<script>
            $(document).ready(function() {
                toastr.options = {
                    'closeButton': true,
                    'progressBar': true,
                    'positionClass': 'toast-bottom-right',
                    'timeOut': '3000'
                };
                toastr.success('Your password has been changed successfully.');
            });
          </script>";
}
?>


  <main>


    <?php
    include('./nav.php');
    ?>


    <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
      <div class="container">
        <div class="row">

          <div class="col-lg-6 col-12 mb-5 pb-5 pb-lg-0 mb-lg-0">

            <h6>Introducing Coa</h6>

            <h1 class="text-white mb-4">COA TSO Special Services Assignment Tracker</h1>

            <a href="#section_2" class="btn custom-btn smoothscroll me-3">Discover More</a>

            <a href="#section_3" class="link link--kale smoothscroll">Meet the Author</a>
          </div>

          <div class="hero-image-wrap col-lg-6 col-12 mt-3 mt-lg-0">
            <img src="images/Atlantis A25.png" class="hero-image img-fluid" alt="COA TSO Special Services Assignment Tracker">
          </div>

        </div>
      </div>
    </section>


    <section class="featured-section">
      <div class="container">
        <div class="row">

          <div class="col-lg-8 col-12">
            <div class="avatar-group d-flex flex-wrap align-items-center">
              <img src="images/avatar/portrait-beautiful-young-woman-standing-grey-wall.jpg" class="img-fluid avatar-image" alt="">

              <img src="images/avatar/portrait-young-redhead-bearded-male.jpg" class="img-fluid avatar-image avatar-image-left" alt="">

              <img src="images/avatar/pretty-blonde-woman.jpg" class="img-fluid avatar-image avatar-image-left" alt="">

              <img src="images/avatar/studio-portrait-emotional-happy-funny-smiling-boyfriend.jpg" class="img-fluid avatar-image avatar-image-left" alt="">

              <div class="reviews-group mt-3 mt-lg-0">
                <strong>4.5</strong>

                <i class="bi-star-fill"></i>
                <i class="bi-star-fill"></i>
                <i class="bi-star-fill"></i>
                <i class="bi-star-fill"></i>
                <i class="bi-star"></i>

                <small class="ms-3">2,564 reviews</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="py-lg-5"></section>


    <section id="section_1">

      <!-- left image -->
      <!-- <div class="blog-card">
        <div class="meta">
          <div class="photo" style="background-image: url(https://storage.googleapis.com/chydlx/codepen/blog-cards/image-1.jpg)"></div>
          <ul class="details">
            <li class="author text-white"><a href="#">user</a></li>
            <li class="date text-white">start July. 15, 2015 - end date July. 16, 2015</li>
            <li class="tags text-white">
              <ul>
                <li><a href="#" class="text-white">sec 1</a></li>
                <li><a href="#" class="text-white">sec 2</a></li>
                <li><a href="#" class="text-white">sec 3</a></li>
              </ul>
            </li>
          </ul>
        </div>
        <div class="description">
          <h1>title</h1>
          <h2>second title</h2>
          <p> description</p>
          <p class="read-more">
            <a href="#">Read More</a>
          </p>
        </div>
      </div> -->
      <style>
        
      </style>
      <?php
$result = $conn->query("SELECT a.id, u.name AS user, a.title, a.second_title, a.description, a.date_start, a.date_end, a.image 
FROM announcements a 
JOIN users u ON a.user_id = u.id 
WHERE a.status = 'active' 
ORDER BY a.updated_at DESC");


// Check if the query has returned any results
if ($result->num_rows > 0) {
?>
    <div class="blog-cards">
        <?php 
        $index = 0; // Start with the first index
        while ($row = $result->fetch_assoc()): 
            // Add 'alt' class for alternating layout
            $class = ($index % 2 == 0) ? 'alt' : ''; // Add 'alt' for even indexes
            ?>
            <div class="blog-card <?= $class ?>">
                <div class="meta">
                    <div class="photo" style="background-image: url(<?= htmlspecialchars($row['image']) ?>)"></div>
                    <ul class="details">
                        <li class="author text-white"><a href="#"><?= htmlspecialchars($row['user']) ?></a></li>
                        <li class="date text-white">
                            <?= htmlspecialchars($row['date_start']) ?> / <?= htmlspecialchars($row['date_end']) ?>
                        </li>
                    </ul>
                </div>
                <div class="description">
                    <h1><?= htmlspecialchars($row['title']) ?></h1>
                    <h2><?= htmlspecialchars($row['second_title']) ?></h2>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <p class="read-more">
                        <a href="#">Read More</a>
                    </p>
                </div>
            </div>
        <?php 
            $index++; // Increment index for the next iteration
        endwhile; ?>
    </div>
<?php
} else {
    echo "<p style='text-align:center'>No announcements found.</p>";
}
?>



    </section>


    <section class="book-section section-padding" id="section_2">
      <div class="container">
        <div class="row">

          <div class="col-lg-6 col-12">
            <img src="images/tablet-screen-contents.jpg" class="img-fluid" alt="">
          </div>

          <div class="col-lg-6 col-12">
            <div class="book-section-info">
              <h6>Modern &amp; Creative</h6>


              <h2 class="mb-4">About The COA</h2>

              <p>Audit report on the results of audit of a national government agency with Regional Offices and field/operating units; and for a government corporation with regional branches and/or field offices.</p>

              <p>Audit report on the results of audit of the regional/branch office, field/operating unit, staff bureau and line office with complete set of books of accounts; or an audit report on agencies with complete set of books of accounts but no financial statements submitted as of the deadline set by COA and league/federation of local government units and local elective officials.</p>
            </div>
          </div>

        </div>
      </div>
    </section>


    <section>
      <div class="container">
        <div class="row">

          <div class="col-lg-12 col-12 text-center">
            <h6>What's inside?</h6>

            <h2 class="mb-5">Preview at glance</h2>
          </div>

          <div class="col-lg-4 col-12">
            <nav id="navbar-example3" class="h-100 flex-column align-items-stretch">
              <nav class="nav nav-pills flex-column">
                <a class="nav-link smoothscroll" href="#item-1">Introduction</a>

                <a class="nav-link smoothscroll" href="#item-2">Chapter 1: <strong>Win back your time</strong></a>

                <a class="nav-link smoothscroll" href="#item-3">Chapter 2: <strong>Work less, do more</strong></a>

                <a class="nav-link smoothscroll" href="#item-4">Chapter 3: <strong>Delegate</strong></a>

                <a class="nav-link smoothscroll" href="#item-5">Chapter 4: <strong>Habits</strong></a>
              </nav>
            </nav>
          </div>

          <div class="col-lg-8 col-12">
            <div data-bs-spy="scroll" data-bs-target="#navbar-example3" data-bs-smooth-scroll="true" class="scrollspy-example-2" tabindex="0">
              <div class="scrollspy-example-item" id="item-1">
                <h5>Introducing Audit Reports</h5>

                <p>Serve as a crucial mechanism for communicating audit findings, recommendations, and insights to auditees, policymakers, and the general public. The reports play a pivotal role in promoting transparency, accountability, and good governance within the public sector.</p>

                <p><strong>Audit Process</strong> Systematic series of steps conducted by the Commission on Audit (COA) to examine and evaluate the financial activities, performance, and compliance of government entities. </p>

                <blockquote class="blockquote">Systematic series of steps conducted by the Commission on Audit (COA) to examine and evaluate the financial activities, performance, and compliance of government entities. </blockquote>

                <p>Encompasses a diverse array of professionals, including auditors, accountants, legal experts, engineers, and administrative staff, all working collaboratively to fulfill the COA's mission.</p>
              </div>

              <div class="scrollspy-example-item" id="item-2">
                <h5>Win back your time</h5>

                <p>An audit opinion pertains only to the financial audit conducted regularly by COA auditors on the agencies within its jurisdiction. It may be unmodified or modified, depending on whether an agency’s financial statements are prepared, in all material respects, in accordance with the applicable financial reporting framework.</p>

                <p>An unmodified opinion (also referred to as unqualified opinion) is issued when the auditor concludes
                  that the financial statements are prepared, in all material respects, in accordance with the applicable
                  financial reporting framework. Meanwhile, a modified opinion – includes qualified, adverse and disclaimer
                  of opinion – is issued when the auditor concludes that, based on the audit evidence obtained, the financial
                  statements as a whole are not free from material misstatement; or is unable to obtain sufficient
                  appropriate audit evidence to conclude that the financial statements as
                  a whole are free from material misstatement.</p>

                <p>It is important to note that the financial statements represent only a facet of an agency and that
                  an audit opinion does not provide any conclusions on the agency’s level of compliance with laws,
                  rules and regulations, nor the application of the principles of economy, efficiency, and
                  effectiveness in the agency’s operations. Such matters are covered by the compliance and
                  performance audits, respectively, which are conducted by the COA in addition to financial
                  audit. Results of these audits are found in the Annual Audit Reports and are uploaded in
                  the COA website.</p>

                <div class="row">
                  <div class="col-lg-6 col-12 mb-3">
                    <img src="images/portrait-mature-smiling-authoress-sitting-desk.jpg" class="scrollspy-example-item-image img-fluid" alt="">
                  </div>

                  <div class="col-lg-6 col-12 mb-3">
                    <img src="images/businessman-sitting-by-table-cafe.jpg" class="scrollspy-example-item-image img-fluid" alt="">
                  </div>
                </div>

                <p>For more information, attached is a briefer on understanding an audit opinion. #</p>
              </div>

              <div class="scrollspy-example-item" id="item-3">
                <h5>Work less, do more</h5>

                <p>COA Commissioner Roland Café Pondoc, who headed the Philippine delegation to the Assembly, said that being
                  elected as a member of the ASOSAI GB is one of the highlights of the Assembly, which involved filling seven
                  vacant posts for the GB. Other countries elected to the GB together with the Philippines were Azerbaijan,
                  Kazakhstan, Korea, Malaysia, Pakistan and the United Arab Emirates.</p>
                <p>COA Commissioner Roland Café Pondoc, who headed the Philippine delegation to the Assembly, said that being
                  elected as a member of the ASOSAI GB is one of the highlights of the Assembly, which involved filling seven
                  vacant posts for the GB. Other countries elected to the GB together with the Philippines were Azerbaijan,
                  Kazakhstan, Korea, Malaysia, Pakistan and the United Arab Emirates.</p>

                <p>COA Commissioner Roland Café Pondoc, who headed the Philippine delegation to the Assembly, said that being
                  elected as a member of the ASOSAI GB is one of the highlights of the Assembly, which involved filling seven
                  vacant posts for the GB. Other countries elected to the GB together with the Philippines were Azerbaijan,
                  Kazakhstan, Korea, Malaysia, Pakistan and the United Arab Emirates.</p>

                <div class="row align-items-center">
                  <div class="col-lg-6 col-12">
                    <img src="images/tablet-screen-contents.jpg" class="img-fluid" alt="">
                  </div>

                  <div class="col-lg-6 col-12">
                    <p>Modern COA ever</p>

                    <p><strong>COA Commissioner Roland Café Pondoc, who headed the Philippine delegation to the Assembly, said that being elected as a member of the ASOSAI GB is one of the highlights of the Assembly, which involved filling seven vacant posts for the GB. Other countries elected to the GB together with the Philippines
                        were Azerbaijan, Kazakhstan, Korea, Malaysia, Pakistan and the United Arab Emirates.</strong></p>
                  </div>
                </div>
              </div>

              <div class="scrollspy-example-item" id="item-4">
                <h5>Delegate</h5>

                <p>Ten Commission on Audit (COA) auditors recently attended the Single Country Audit Capacity Training
                  Program hosted by the Board of Audit and Inspection (BAI) of South Korea,
                  through its Audit and Investigation Training Institute (AITI).</p>

                <p>Held in Seoul, South Korea from 17 to 27 June 2024, the training was facilitated by the COA International
                  Audit and Relations Office. The COA participants in the training program were Assistant Directors
                  Marilyn B. Miran from the Local Government Audit Sector,</p>

                <p>Richard M. Fulleros from the Corporate Government Audit Sector (CGAS), and Abdul Nassif M. Faisal from CGAS,
                  Atty. Elizabeth P. De Vera from COA Regional Office No. I, Ms. Maria Sharon B. Reyes from COA Regional Office
                  No. V, Mr. John Gilbert P. Pio</p>

                <img src="images/portrait-mature-smiling-authoress-sitting-desk.jpg" class="scrollspy-example-item-image img-fluid mb-3" alt="">

                <p>from the National Government Audit Sector (NGAS), Ms. Mary Grace S. Calamba from NGAS, Ms. Maria Shella P. Malabanan from CGAS,
                  Ms. Vida Nathania L. Pimentel from CGAS, and Ms. Jouella Kay A. Carag from CGAS.</p>
              </div>

              <div class="scrollspy-example-item" id="item-5">
                <h5>Core Values</h5>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

                <p>You are not allowed to redistribute this template ZIP file on any other template collection website. Please contact TemplateMo for more information.</p>

                <p><strong>God Centeredness</strong> We commit to serve the interest of the Filipino people and the country
                  which shall have priority over all other considerations.</p>

                <p><strong>Patriotism</strong> We commit to serve the interest of the Filipino people and the country
                  which shall have priority over all other considerations.</p>

                <p><strong>Excellence</strong> We pursue world-class audit services availing of state-of-the-art technology in conformity
                  with international standards and best practices.</p>

                <p><strong>Integrity</strong> We discharge our mandate in adherence to moral and ethical principles
                  and the highest degree of honesty, independence, objectivity and professionalism.</p>

                <p><strong>Professionalism</strong> We believe in the continuous enhancement of the skills, competence and expertise of our personnel in the basic
                  right of every member of the organization to self-development and well being.</p>

                <p><strong>Courtesy, Modesty and Humility</strong> We uphold and practice courtesy, modesty and humility at all times, and
                  acknowledge that we do not have a monopoly of technical expertise.</p>

                <p><strong>Reverence for Truth and the Rule of Law</strong>We pledge to remain steadfast in our sworn duty to uphold COA’s
                  ideals out of reverence for truth and the rule of law.</p>

                <blockquote class="blockquote">Lorem Ipsum dolor sit amet, consectetur adipsicing kengan omeg kohm tokito</blockquote>

                <p>Lorem Ipsum dolor sit amet, consectetur adipsicing kengan omeg kohm tokito</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>


    <section class="author-section section-padding" id="section_3">
      <div class="container">
        <div class="row">

          <div class="col-lg-6 col-12">
            <img src="images/portrait-mature-smiling-authoress-sitting-desk.jpg" class="author-image img-fluid" alt="">
          </div>

          <div class="col-lg-6 col-12 mt-5 mt-lg-0">
            <h6>Meet Author</h6>

            <h2 class="mb-4">Mrs. Liene</h2>

            <p>Lorem Ipsum dolor sit amet, consectetur adipsicing kengan omeg kohm tokito</p>

            <p>Lorem ipsum dolor sit amet, consive adipisicing elit, sed do eiusmod. tempor incididunt ut labore.</p>
          </div>

        </div>
      </div>
    </section>


    <section class="reviews-section section-padding" id="section_4">
      <div class="container">
        <div class="row">

          <div class="col-lg-12 col-12 text-center mb-5">
            <h6>Commision on Audit</h6>

            <h2>Organization Chart</h2>
            <p>Illustrates the hierarchical structure of the Commission on Audit, depicting
              the various officers and sectors within the organization.</p>
          </div>
          <div class="org">
            <img src="images/coa-orgchart-new.png" class="scrollspy-example-item-image img-fluid mb-3" alt="">
          </div>
        </div>
      </div>
    </section>


    <?php
    include('./footer.php');
    ?>
  </main>

  <?php
include ('./modals.php');
?>
  <!-- JAVASCRIPT FILES -->
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/jquery.sticky.js"></script>
  <script src="js/click-scroll.js"></script>
  <script src="js/custom.js"></script>
  <script src="js/clock.js"></script>
  <script src="js/logout_alert.js"></script>
</body>

</html>