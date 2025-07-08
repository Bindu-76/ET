<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

require_once "config.php";

$user_id = $_SESSION['user_id'];

// Handle form submission for rating & comment
$feedback_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment'] ?? '');

    // Validate rating
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO ratings (user_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $rating, $comment);
        if ($stmt->execute()) {
            $feedback_msg = "Thank you for your feedback!";
        } else {
            $feedback_msg = "Error saving feedback. Please try again.";
        }
        $stmt->close();
    } else {
        $feedback_msg = "Invalid rating value.";
    }
}

// Get user basic info
$query = $conn->prepare("SELECT name, subscription, profile_photo FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($name, $subscription, $profile_photo);
$query->fetch();
$query->close();

// Set default profile photo if missing or not uploaded
if (!$profile_photo || !file_exists("uploads/" . $profile_photo)) {
  $profile_photo = "uploads/default.png";
}

// Fetch latest subscription details (if needed)
/*
$subs_stmt = $conn->prepare("SELECT requested_at, activated_at FROM subscriptions WHERE user_id = ? ORDER BY requested_at DESC LIMIT 1");
$subs_stmt->bind_param("i", $user_id);
$subs_stmt->execute();
$subs_result = $subs_stmt->get_result();
$current_sub = $subs_result->fetch_assoc();
$subs_stmt->close();
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Home | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fffaf2;
      margin: 0;
      padding: 0;
    }
    /* Sidebar styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 280px;
      background: colorrgb(49, 62, 240);
      color: white;
      padding-top: 30px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      align-items: center;
      overflow-y: auto;
      z-index: 1000;
    }
    .profile-photo-sidebar {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #fff;
      margin-bottom: 40px;
      box-shadow: 0 0 10pxrgb(78, 69, 214);
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .profile-photo-sidebar:hover {
      transform: scale(1.1);
      box-shadow: 0 0 15px rgba(117, 95, 141, 0.9);
    }
    .sidebar a {
      color:rgb(17, 17, 17);
      text-decoration: none;
      display: block;
      padding: 18px 25px;
      font-weight: bold;
      box-shadow: 2px 2px 8px rgba(4, 8, 8, 0.96);
      margin: 15px 0;
      border-radius: 8px;
      background-color:rgb(57, 46, 201);
      width: 85%;
      text-align: center;
      transition: transform 0.2s ease, background-color 0.3s ease;
      user-select: none;
    }
    .sidebar a:hover {
      background-color: rgba(255, 255, 255, 0);
      transform: scale(1.05);
    }
    /* Main content styles */
    .main {
      margin-left: 280px; /* same as sidebar width */
      padding: 30px 40px;
      min-height: 100vh;
      background-color: #fffaf2;
    }
    .section {
      padding: 40px 0;
      border-bottom: 1px solid #eee;
    }
    h2, h3 {
      text-align: center;
      margin-bottom: 10px;
      color: #333;
    }
    p.text-center {
      font-size: 1.1rem;
      color: #555;
    }
    .bio-img {
      max-width: 100%;
      border-radius: 10px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    .video-embed iframe {
      width: 100%;
      height: 315px;
      border: none;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .subscription-card .card {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
      transition: transform 0.3s ease;
    }
    .subscription-card .card:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .footer {
      text-align: center;
      padding: 20px 0;
      font-weight: 600;
      color: #777;
      background-color: #f7f7f7;
      margin-top: 40px;
      border-top: 1px solid #ddd;
    }
    .rating-stars input[type="radio"] {
      display: none;
    }
    .rating-stars label {
      font-size: 2.5rem;
      color: lightgray;
      cursor: pointer;
      user-select: none;
      transition: color 0.2s ease-in-out;
    }
    .rating-stars input:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
      color: gold;
    }
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        flex-direction: row;
        padding: 10px 0;
        overflow-x: auto;
      }
      .profile-photo-sidebar {
        width: 70px;
        height: 70px;
        margin-bottom: 0;
        margin-left: 10px;
      }
      .sidebar a {
        margin: 0 10px;
        padding: 10px 15px;
        width: auto;
        font-size: 14px;
      }
      .main {
        margin-left: 0;
        padding: 20px;
      }
      .video-embed iframe {
        height: 200px;
      }
    }
  </style>
</head>
<body>

  <nav class="sidebar" role="navigation" aria-label="Sidebar Navigation">
    <a href="profile.php" title="Go to your profile">
      <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo of <?php echo htmlspecialchars($name); ?>" class="profile-photo-sidebar" />
    </a>
    <a href="schedule.php">📅 Schedule</a>
    <a href="video.php">🎥 Videos</a>
    <a href="workshop_user.php">🛠️ Workshops</a>
    <a href="pose.php">🧘 Poses</a>
     <a href="mudras.php">Mudras</a> 
    <a href="logout.php">🚪 Logout</a>
  </nav>

  <main class="main" role="main">

    <section class="section">
      <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
      <p class="text-center">Your subscription: <strong><?php echo htmlspecialchars($subscription); ?></strong></p>
    </section>

    <!-- Slideshow -->
    <section class="section">
      <h3>Yoga Workshop Images</h3>
      <div id="slideshow" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="images/p14.jpg" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="Workshop Image 1">
          </div>
          <div class="carousel-item">
            <img src="images/p16.jpg" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="Workshop Image 2">
          </div>
          <div class="carousel-item">
            <img src="images/p11.jpg" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="Workshop Image 3">
          </div>
          <div class="carousel-item">
            <img src="images/p15 mor.jpg" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="Workshop Image 4">
          </div>
          <div class="carousel-item">
            <img src="images/p20.jpg" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="Workshop Image 5">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#slideshow" data-bs-slide="prev" aria-label="Previous Slide">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#slideshow" data-bs-slide="next" aria-label="Next Slide">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </section>

    <!-- Subscription Plans -->
    <section class="section">
      <h3>Subscription Area</h3>
      <div class="row justify-content-center">
        <?php
        $plans = [
          ['See', 'plans', 'to'],
        ];
        foreach ($plans as $plan): ?>
          <div class="col-md-3 subscription-card mb-4">
            <div class="card">
              <div class="card-body text-center">
                <h5 class="card-title"><?php echo $plan[0]; ?></h5>
                <p>₹<?php echo $plan[1]; ?></p>
                <a href="subscribe.php?plan=<?php echo $plan[2]; ?>" class="btn btn-success">Join</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Instructor Bio -->
    <section class="section">
      <h3>About Your Instructor</h3>
      <div class="row">
        <div class="col-md-4">
          <img src="images/p26 hover.jpg" class="img-fluid bio-img" alt="Prashanth Ramu">
        </div>
        <div class="col-md-8">
            <p> I'm Prashanth Ramu, and I’m a yoga teacher and practitioner with over 15 years of experience. I specialize in Ashtanga Vinyasa Yoga, Vinyasa Yoga, Hatha Yoga, Yogic Kriyas, Pranayamas, and Bandhas. My yoga journey began in childhood, introduced by my father, and has since evolved into a lifelong path of deep practice and teaching.

I currently run a yoga school in Mysuru, India, and teach both in-person and online classes to a global student base.</p>

<p>My offerings include daily group classes, personal sessions, international retreats, teacher training programs, and specialized workshops focused on biomechanics, alignment, and injury prevention.

Beyond teaching, I actively create educational yoga content for YouTube and social media, aiming to make authentic and structured yoga practice accessible to all levels of practitioners.


I’m now working on building a comprehensive yoga app that will deliver guided sessions, practice tracking, and a connected community experience—reflecting the core values of traditional wisdom and modern science
        </p>
        </div>
      </div>
    </section>

    <!-- Featured Videos -->
    <section class="section">
      <h3>Featured Videos of Prashanth</h3>
      <div class="row video-embed">
        <div class="col-md-6 mb-3">
          <iframe src="https://youtube.com/shorts/wpbgK7ye2GU?si=-coPTohMTTiZ7UWB" allowfullscreen title="Yoga Video 1"></iframe>
        </div>
        <div class="col-md-6 mb-3">
          <iframe src="https://youtube.com/shorts/Rmlldb1Ya58?si=wwHIaV0-I-tmpmGK" allowfullscreen title="Yoga Video 2"></iframe>
        </div>
      </div>
    </section>

    <!-- Contact & Social -->
    <section class="section">
      <p class="text-center">Email: <a href="mailto:bindumm237@gmail.com">bindumm237@gmail.com</a></p>
      <p class="text-center">Instagram: <a href="https://www.instagram.com/prashantha.yoga/" target="_blank" rel="noopener noreferrer">@ekatatvawellness</a></p>
      <p class="text-center">YouTube: <a href="https://youtube.com/@prashanthayoga?si=mNZ2XXnuUgh95hoQ" target="_blank" rel="noopener noreferrer">Eka Tatva Wellness</a></p>
      <p class="text-center">Facebook:<a href="facebook.com/prashiyoga" target="_blank" rel="noopener noreferrer">Eka Tatva Wellness</a></p>
      <p class="text-center">Phone: +91-9164421576</p>
      <div class="text-center">
        <img src="images/p25logo.jpg" alt="Eka Tatva Logo" style="height: 60px;">
      </div>
    </section>

    <!-- Rate Your Experience -->
    <section class="section">
      <h3 class="text-center mb-4">🌟 Rate Your Experience with Eka Tatva</h3>

      <?php if ($feedback_msg): ?>
        <div class="alert alert-info text-center"><?php echo htmlspecialchars($feedback_msg); ?></div>
      <?php endif; ?>

      <form action="home.php" method="POST" class="text-center" novalidate>
        <div class="mb-4 rating-stars d-flex justify-content-center gap-3">
          <input type="radio" name="rating" id="star5" value="5" required hidden />
          <label for="star5" title="Excellent" style="font-size: 2.5rem; cursor: pointer;">⭐</label>

          <input type="radio" name="rating" id="star4" value="4" hidden />
          <label for="star4" title="Very Good" style="font-size: 2.5rem; cursor: pointer;">⭐</label>

          <input type="radio" name="rating" id="star3" value="3" hidden />
          <label for="star3" title="Good" style="font-size: 2.5rem; cursor: pointer;">⭐</label>

          <input type="radio" name="rating" id="star2" value="2" hidden />
          <label for="star2" title="Fair" style="font-size: 2.5rem; cursor: pointer;">⭐</label>

          <input type="radio" name="rating" id="star1" value="1" hidden />
          <label for="star1" title="Poor" style="font-size: 2.5rem; cursor: pointer;">⭐</label>
        </div>

        <div class="mb-3">
          <label for="comment" class="form-label">💬 Share your thoughts</label>
          <textarea name="comment" id="comment" class="form-control mx-auto" style="max-width: 500px;" rows="3" placeholder="Your feedback helps us to grow..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary px-4 mt-2">Submit</button>
      </form>

      <p class="text-center mt-3 text-muted" style="font-size: 0.9rem;">
        Your feedback is stored securely and accessible only to the admin panel for quality improvements.
      </p>
    </section>

    <!-- Footer -->
    <footer class="footer">
      All rights reserved © 2025 Eka Tatva Wellness
    </footer>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Star rating highlight script -->
  <script>
    const stars = document.querySelectorAll('.rating-stars label');
    stars.forEach((star, index) => {
      star.addEventListener('mouseover', () => {
        stars.forEach((s, i) => {
          s.style.color = i <= index ? '#FFD700' : 'lightgray';
        });
      });
      star.addEventListener('mouseout', () => {
        const checked = document.querySelector('.rating-stars input[type="radio"]:checked');
        if (!checked) {
          stars.forEach(s => s.style.color = 'lightgray');
        } else {
          const checkedIndex = Array.from(stars).indexOf(document.querySelector(`label[for="${checked.id}"]`));
          stars.forEach((s, i) => {
            s.style.color = i <= checkedIndex ? '#FFD700' : 'lightgray';
          });
        }
      });
      star.addEventListener('click', () => {
        stars.forEach((s, i) => {
          s.style.color = i <= index ? '#FFD700' : 'lightgray';
        });
      });
    });
  </script>
</body>
</html>

