<?php

namespace App\Console\Commands;

use Faker\Factory;
use Illuminate\Console\Command;
use RdKafka\Producer;
use RdKafka\Conf;
class FakeKafkaProduceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:fake-produce';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $fake = Factory::create();
        // Tạo dữ liệu giả
        $userData = [
            'name' => $fake->name,
            'email' => $fake->email,
            'password' => $fake->password,
        ];

        // Chuyển dữ liệu thành JSON
        $message = json_encode($userData);

        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKER'));

        // Tạo Kafka Producer
        $producer = new Producer($conf);

        // Chọn topic
        $topic = $producer->newTopic(env('KAFKA_TOPIC'));
        try {
            // Gửi message vào topic "test"
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

            // Đảm bảo gửi tin nhắn đã hoàn tất
            $producer->flush(10000); // Timeout 10 giây để gửi hoàn tất

            $this->info("Message sent to Kafka: " . $message);
        } catch (\Exception $e) {
            $this->error("Error sending message: " . $e->getMessage());
        }
    }
}
