<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header, footer {
            background-color: #004d99;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        header h1, footer p {
            margin: 0;
            font-size: 24px;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .hotel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .hotel-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 15px 25px rgba(0, 0, 0, 0.2);
        }
        .hotel-card h2 {
            color: #004d99;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .hotel-card p {
            margin: 5px 0;
            color: #666;
        }
        .hotel-card .facilities {
            font-style: italic;
        }
        .hotel-card .map-link {
            display: block;
            margin: 10px 0;
            color: #004d99;
            text-decoration: none;
            font-weight: bold;
        }
        .hotel-card .images {
            margin-top: 10px;
        }
        .images img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
        }
        .slider {
            position: relative;
            max-width: 100%;
            margin: auto;
            overflow: hidden;
        }
        .slides {
            display: flex;
            transition: transform 0.5s ease;
        }
        .slide {
            min-width: 100%;
            box-sizing: border-box;
            
        }
       
        .slide img {
            width: 100%;
            height: 300px;
        }
        .arrow {
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            cursor: pointer;
            user-select: none;
            border-radius: 0 3px 3px 0;
            background-color: rgba(0,0,0,0.8);
        }
        .arrow.left {
            left: 0;
            border-radius: 3px 0 0 3px;
        }
        .arrow.right {
            right: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to the Hotels Information</h1>
    </header>
    <div class="container">
        <div class="hotel-grid">
            <?php
            // Read and decode JSON file
            $jsonFilePath = 'testfile.json';
            $jsonData = file_get_contents($jsonFilePath);
            $hotels = json_decode($jsonData, true);

            if (!empty($hotels)) {
                foreach ($hotels as $index => $hotel) {
                    echo "<div class='hotel-card'>";
                    echo "<h2>" . htmlspecialchars($hotel['HotelName']) . "</h2>";
                    echo "<p><strong>Rating:</strong> " . htmlspecialchars($hotel['HotelRating']) . " stars</p>";
                    echo "<p><strong>Address:</strong> " . htmlspecialchars($hotel['Address']) . "</p>";
                    echo "<p><strong>Country:</strong> " . htmlspecialchars($hotel['CountryName']) . "</p>";
                    echo "<p><strong>City Name:</strong> " . htmlspecialchars($hotel['CityName']) . "</p>";
                    echo "<p><strong>Pin Code:</strong> " . htmlspecialchars($hotel['PinCode']) . "</p>";
                    echo "<p><strong>Phone Number:</strong> " . htmlspecialchars($hotel['PhoneNumber']) . "</p>";
                    echo "<p><strong>Description:</strong> " . htmlspecialchars_decode($hotel['Description']) . "</p>";
                    echo "<p class='facilities'><strong>Facilities:</strong> " . htmlspecialchars(implode(', ', $hotel['HotelFacilities'])) . "</p>";
                    echo "<p><a href='" . htmlspecialchars($hotel['HotelWebsiteUrl']) . "' target='_blank' class='map-link'>Visit Hotel Website</a></p>";
                    echo "<p><a href='https://www.google.com/maps/search/?api=1&query=" . htmlspecialchars($hotel['Map']) . "' target='_blank' class='map-link'>View on Map</a></p>";

                    if (!empty($hotel['Rooms'])) {
                        foreach ($hotel['Rooms'] as $room) {
                            echo "<h2><strong>Available Rooms</strong></h2>";
                            echo "<p><strong>Name:</strong> " . htmlspecialchars(implode(', ', $room['Name'])) . "</p>";
                            echo "<p><strong>Inclusion:</strong> " . htmlspecialchars($room['Inclusion']) . "</p>";
                            echo "<p><strong>Price per Night:</strong> " . htmlspecialchars($room['TotalFare']) . "</p>";
                            echo "<p><strong>Meal Type:</strong> " . htmlspecialchars($room['MealType']) . "</p>";
                            echo "<p><strong>Cancellation Policy:</strong> " . htmlspecialchars(implode(', ', array_column($room['CancelPolicies'], 'ChargeType'))) . "</p>";
                            echo "<p><strong>Refundable:</strong> " . ($room['IsRefundable'] ? "Yes" : "No") . "</p>";
                        }
                    }
                    // Display Room Images
                    if (!empty($hotel['Images'])) {
                        echo "<div class='slider' id='slider-$index'>";
                        echo "<div class='slides' id='slides-$index'>";
                        foreach ($hotel['Images'] as $image) {
                            echo "<div class='slide'><img  src='" . htmlspecialchars($image) . "' alt='Room Image'></div>";
                        }
                        echo "</div>";
                        echo "<a class='arrow left' onclick='prevSlide($index)'>❮</a>";
                        echo "<a class='arrow right' onclick='nextSlide($index)'>❯</a>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            }else {
                echo "<p>No hotel data found.</p>";
            }
            ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Hotel Information System. All rights reserved.</p>
    </footer>
    <script>
          const currentSlide = {};

function showSlide(sliderId, index) {
    const slides = document.querySelector(`#slides-${sliderId}`);
    const totalSlides = slides.querySelectorAll('.slide').length;
    if (index >= totalSlides) {
        currentSlide[sliderId] = 0;
    } else if (index < 0) {
        currentSlide[sliderId] = totalSlides - 1;
    } else {
        currentSlide[sliderId] = index;
    }
    slides.style.transform = `translateX(${-currentSlide[sliderId] * 100}%)`;
}

function nextSlide(sliderId) {
    showSlide(sliderId, (currentSlide[sliderId] || 0) + 1);
}

function prevSlide(sliderId) {
    showSlide(sliderId, (currentSlide[sliderId] || 0) - 1);
}
    </script>
</body>
</html>
