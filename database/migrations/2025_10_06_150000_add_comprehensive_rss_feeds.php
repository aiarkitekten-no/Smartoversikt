<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add comprehensive RSS feeds in various categories
        $feeds = [
            // Norske Nyheter
            ['name' => 'Dagbladet', 'url' => 'https://www.dagbladet.no/rss', 'category' => 'Nyheter - Norge'],
            ['name' => 'NRK Norge', 'url' => 'https://www.nrk.no/norge/toppsaker.rss', 'category' => 'Nyheter - Norge'],
            ['name' => 'TV2 Nyheter', 'url' => 'https://www.tv2.no/rss', 'category' => 'Nyheter - Norge'],
            ['name' => 'ABC Nyheter', 'url' => 'https://www.abcnyheter.no/rss', 'category' => 'Nyheter - Norge'],
            ['name' => 'Nettavisen', 'url' => 'https://www.nettavisen.no/rss/', 'category' => 'Nyheter - Norge'],
            
            // Økonomi & Business
            ['name' => 'E24', 'url' => 'https://e24.no/rss', 'category' => 'Økonomi'],
            ['name' => 'Dagens Næringsliv', 'url' => 'https://www.dn.no/rss', 'category' => 'Økonomi'],
            ['name' => 'Finansavisen', 'url' => 'https://finansavisen.no/feed', 'category' => 'Økonomi'],
            ['name' => 'Kapital', 'url' => 'https://kapital.no/feed', 'category' => 'Økonomi'],
            
            // Teknologi
            ['name' => 'Tek.no', 'url' => 'https://www.tek.no/rss', 'category' => 'Teknologi'],
            ['name' => 'Digi.no', 'url' => 'https://www.digi.no/rss', 'category' => 'Teknologi'],
            ['name' => 'Ars Technica', 'url' => 'https://feeds.arstechnica.com/arstechnica/index', 'category' => 'Teknologi'],
            ['name' => 'TechCrunch', 'url' => 'https://techcrunch.com/feed/', 'category' => 'Teknologi'],
            ['name' => 'The Verge', 'url' => 'https://www.theverge.com/rss/index.xml', 'category' => 'Teknologi'],
            ['name' => 'Wired', 'url' => 'https://www.wired.com/feed/rss', 'category' => 'Teknologi'],
            
            // Sport
            ['name' => 'VG Sport', 'url' => 'https://www.vg.no/rss/create.php?categories=10', 'category' => 'Sport'],
            ['name' => 'TV2 Sport', 'url' => 'https://www.tv2.no/sport/rss', 'category' => 'Sport'],
            ['name' => 'NRK Sport', 'url' => 'https://www.nrk.no/sport/toppsaker.rss', 'category' => 'Sport'],
            ['name' => 'Eurosport', 'url' => 'https://www.eurosport.no/rss.xml', 'category' => 'Sport'],
            
            // Internasjonale Nyheter
            ['name' => 'BBC News', 'url' => 'http://feeds.bbci.co.uk/news/rss.xml', 'category' => 'Internasjonalt'],
            ['name' => 'CNN', 'url' => 'http://rss.cnn.com/rss/edition.rss', 'category' => 'Internasjonalt'],
            ['name' => 'The Guardian', 'url' => 'https://www.theguardian.com/world/rss', 'category' => 'Internasjonalt'],
            ['name' => 'Reuters', 'url' => 'https://www.reutersagency.com/feed/', 'category' => 'Internasjonalt'],
            ['name' => 'Al Jazeera', 'url' => 'https://www.aljazeera.com/xml/rss/all.xml', 'category' => 'Internasjonalt'],
            
            // Kultur & Underholdning
            ['name' => 'NRK Kultur', 'url' => 'https://www.nrk.no/kultur/toppsaker.rss', 'category' => 'Kultur'],
            ['name' => 'Dagbladet Kultur', 'url' => 'https://www.dagbladet.no/rss/kultur', 'category' => 'Kultur'],
            ['name' => 'VG Film', 'url' => 'https://www.vg.no/rss/create.php?categories=72', 'category' => 'Kultur'],
            
            // Vitenskap
            ['name' => 'Forskning.no', 'url' => 'https://forskning.no/rss', 'category' => 'Vitenskap'],
            ['name' => 'NRK Vitenskap', 'url' => 'https://www.nrk.no/viten/toppsaker.rss', 'category' => 'Vitenskap'],
            ['name' => 'Scientific American', 'url' => 'http://rss.sciam.com/ScientificAmerican-Global', 'category' => 'Vitenskap'],
            ['name' => 'Nature News', 'url' => 'https://www.nature.com/nature.rss', 'category' => 'Vitenskap'],
            
            // Gaming
            ['name' => 'IGN', 'url' => 'https://feeds.feedburner.com/ign/all', 'category' => 'Gaming'],
            ['name' => 'GameSpot', 'url' => 'https://www.gamespot.com/feeds/mashup/', 'category' => 'Gaming'],
            ['name' => 'Eurogamer', 'url' => 'https://www.eurogamer.net/?format=rss', 'category' => 'Gaming'],
            
            // Motor & Bil
            ['name' => 'Bil24', 'url' => 'https://bil24.no/feed/', 'category' => 'Motor'],
            ['name' => 'Motor.no', 'url' => 'https://www.motor.no/rss', 'category' => 'Motor'],
            ['name' => 'Elbil24', 'url' => 'https://elbil24.no/feed/', 'category' => 'Motor'],
        ];

        foreach ($feeds as $feed) {
            DB::table('rss_feeds')->insert([
                'name' => $feed['name'],
                'url' => $feed['url'],
                'category' => $feed['category'],
                'is_active' => true,
                'refresh_interval' => 600,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Remove added feeds (keep original 3)
        DB::table('rss_feeds')
            ->whereNotIn('name', ['NRK Nyheter', 'VG', 'Aftenposten'])
            ->delete();
    }
};
