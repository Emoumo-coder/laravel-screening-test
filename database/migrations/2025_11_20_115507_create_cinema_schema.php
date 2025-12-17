<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    ToDo: Create a migration that creates all tables for the following user stories

    For an example on how a UI for an api using this might look like, please try to book a show at https://in.bookmyshow.com/.
    To not introduce additional complexity, please consider only one cinema.

    Please list the tables that you would create including keys, foreign keys and attributes that are required by the user stories.

    ## User Stories

    **Movie exploration**
    * As a user I want to see which films can be watched and at what times
    * As a user I want to only see the shows which are not booked out

    **Show administration**
    * As a cinema owner I want to run different films at different times
    * As a cinema owner I want to run multiple films at the same time in different showrooms

    **Pricing**
    * As a cinema owner I want to get paid differently per show
    * As a cinema owner I want to give different seat types a percentage premium, for example 50 % more for vip seat

    **Seating**
    * As a user I want to book a seat
    * As a user I want to book a vip seat/couple seat/super vip/whatever
    * As a user I want to see which seats are still available
    * As a user I want to know where I'm sitting on my ticket
    * As a cinema owner I don't want to configure the seating for every show
    */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Movies table - stores film information
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->string('genre')->nullable();
            $table->string('language')->nullable();
            $table->string('rating')->nullable();
            $table->string('poster_url')->nullable();
            $table->timestamps();
        });

        // 2. Showrooms table - different rooms in cinema
        Schema::create('showrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('total_seats');
            $table->text('seat_layout')->nullable(); // JSON storing seat arrangement
            $table->timestamps();
        });

        // 3. Seat_types table - different types of seats (normal, vip, couple, etc.)
        Schema::create('seat_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // normal, vip, couple, super_vip
            $table->decimal('premium_percentage', 5, 2)->default(0); // e.g., 50.00 for 50% premium
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Shows table - specific showtimes for movies
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->foreignId('showroom_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('base_price', 8, 2); // Base price for this show
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('start_time');
            $table->index(['movie_id', 'start_time']);
        });

        // 5. Seats table - physical seats in showrooms
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_type_id')->constrained()->onDelete('cascade');
            $table->string('row_number'); 
            $table->integer('seat_number'); 
            $table->string('position')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['showroom_id', 'row_number', 'seat_number']);
        });

        // 6. Bookings table - tracks user bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('booking_reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('confirmed'); // confirmed, cancelled, completed
            $table->timestamps();
            
            $table->index('booking_reference');
            $table->index(['show_id', 'status']);
        });

        // 7. Booking_seats table - which seats are booked in which booking
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_id')->constrained()->onDelete('cascade');
            $table->foreignId('show_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2); // Actual price paid (base + seat premium)
            $table->timestamps();
            
            $table->unique(['show_id', 'seat_id']); // Prevent double booking
            $table->index(['show_id', 'seat_id']);
        });

        // 8. Prices table (optional but good for flexibility)
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_type_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->timestamps();
            
            $table->unique(['show_id', 'seat_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
        Schema::dropIfExists('booking_seats');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('shows');
        Schema::dropIfExists('seat_types');
        Schema::dropIfExists('showrooms');
        Schema::dropIfExists('movies');
    }
};
