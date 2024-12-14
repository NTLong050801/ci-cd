<?php

namespace App\Console\Commands;

use App\Jobs\KafkaMessageJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class KafkaConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:get-consumer';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from Kafka and push them to the queue';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
//    public function handle()
//    {
//        try {
//            // Cấu hình Kafka Consumer
//            $conf = new \RdKafka\Conf();
//            $conf->set('metadata.broker.list', env('KAFKA_BROKER'));
//            $conf->set('enable.auto.commit', 'true'); // Tắt commit tự động để kiểm soát commit offset
//
//            // Khởi tạo Kafka Consumer
//            $consumer = new \RdKafka\Consumer($conf);
//            $topic = $consumer->newTopic(env('KAFKA_TOPIC'));
//            $partition = 0;
//
//            // Đọc từ offset cuối cùng, lấy tin nhắn mới nhất
//            $topic->consumeStart($partition, RD_KAFKA_OFFSET_BEGINNING); // Lấy tin nhắn mới nhất
//
//            while (true) {
//                // Lấy 1 tin nhắn với timeout 1000ms
//                $message = $topic->consume($partition, 1000);
//
//                // Kiểm tra nếu không có message hoặc có lỗi
//                if ($message === null) {
//                    continue; // Nếu không có message mới, tiếp tục vòng lặp
//                }
//
//                if ($message->err) {
//                    $this->error("Error: " . $message->errstr());
//                    continue; // Tiếp tục vòng lặp khi có lỗi
//                }
//
//                // Giải mã payload JSON
//                $messageData = json_decode($message->payload, true); // true để chuyển đổi thành mảng
//
//                // Lưu message vào cơ sở dữ liệu (User example)
//                try {
//                    User::create([
//                        'name' => $messageData['name'],
//                        'email' => $messageData['email'],
//                        'password' => Hash::make($messageData['password']),
//                    ]);
//                }catch (\Exception $e){
//                    $this->error("Error: " . $e->getMessage());
//                }
//
//                // Commit offset sau khi xử lý message
////                $topic->commit($message); // Commit offset sau khi xử lý message
//
//                $this->info("Processed 1 message.");
//            }
//
//        } catch (\Exception $e) {
//            $this->error("Error: " . $e->getMessage());
//        }
//    }

    public function handle()
    {
        try {
            $conf = new \RdKafka\Conf();
            $conf->set('group.id', 'test-group');
            $conf->set('enable.partition.eof', 'true');

            $rk = new \RdKafka\Consumer($conf);
            $rk->addBrokers(env('KAFKA_BROKER'));

            $queue = $rk->newQueue();

            $topicConf = new \RdKafka\TopicConf();
            $topicConf->set('auto.commit.interval.ms', 100);
            $topicConf->set('offset.store.method', 'broker');
            $topicConf->set('auto.offset.reset', 'earliest');

//            $topic = $rk->newTopic("test", $topicConf);
//            $topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

            $topic1 = $rk->newTopic("test", $topicConf);
            $topic1->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);


            $topic2 = $rk->newTopic("ai_send_data_to_web", $topicConf);
            $topic2->consumeQueueStart(0, RD_KAFKA_OFFSET_BEGINNING, $queue);
            $topic2->consumeQueueStart(1, RD_KAFKA_OFFSET_BEGINNING, $queue);



//            while (true) {
//                // Lấy 1 tin nhắn với timeout 1000ms
//                $message = $topic->consume(0, 1000);
//
//                // Kiểm tra nếu không có message hoặc có lỗi
//                if ($message === null) {
//                    continue; // Nếu không có message mới, tiếp tục vòng lặp
//                }
//
//                if ($message->err) {
//                    $this->error("Error: " . $message->errstr());
//                    continue; // Tiếp tục vòng lặp khi có lỗi
//                }
//
//                // Giải mã payload JSON
//                $messageData = json_decode($message->payload, true); // true để chuyển đổi thành mảng
//
//                // Lưu message vào cơ sở dữ liệu (User example)
//                try {
//                    User::create([
//                        'name' => $messageData['name'],
//                        'email' => $messageData['email'],
//                        'password' => Hash::make($messageData['password']),
//                    ]);
//                }catch (\Exception $e){
//                    $this->error("Error: " . $e->getMessage());
//                }
//
//                // Commit offset sau khi xử lý message
////                $topic->commit($message); // Commit offset sau khi xử lý message
//
//                $this->info("Processed 1 message.");
//            }

            while (true) {
                $message = $queue->consume(120*1000);
                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        var_dump($message);
                        break;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        echo "No more messages; will wait for more\n";
                        break;
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        echo "Timed out\n";
                        break;
                    default:
                        throw new \Exception($message->errstr(), $message->err);
                        break;
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }


}
