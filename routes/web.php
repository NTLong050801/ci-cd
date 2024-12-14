<?php

use Illuminate\Support\Facades\Route;
use RdKafka\Consumer;
use RdKafka\Producer;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('kafka');
});



Route::post('/produce', function (Illuminate\Http\Request $request) {
    if (!extension_loaded('rdkafka')) {
        return response()->json('rdkafka extension is not installed.', 500);
    }

    try {
        $message = $request->input('message', 'Default message');

        $conf = new RdKafka\Conf();
        $conf->set("metadata.broker.list", "171.244.129.211:31003");
        $producer = new RdKafka\Producer($conf);
        $topic = $producer->newTopic("test");
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $producer->flush(20000);

        return response()->json("Message sent to Kafka: $message");
    } catch (\Exception $e) {
        return response()->json("Error sending message: " . $e->getMessage(), 500);
    }
});

Route::get('/consume', function () {
    if (!extension_loaded('rdkafka')) {
        return response()->json('rdkafka extension is not installed.', 500);
    }

    try {
        // Cấu hình Kafka Consumer
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', '171.244.129.211:31003'); // Đảm bảo broker IP đúng
//        $conf->set('group.id', 'test-consumer-group');  // Đặt tên cho consumer group của bạn
//        $conf->set('enable.auto.commit', 'true'); // Tự động commit offset sau khi nhận dữ liệu
         $conf->set('group.id', 'ai_send_data_to_web');  // Đặt tên cho consumer group của bạn
        $conf->set('enable.auto.commit', 'false'); // Tự động commit offset sau khi nhận dữ liệu

        // Khởi tạo Kafka Consumer
        $consumer = new RdKafka\Consumer($conf);

        // Đăng ký topic và partition
        $topic = $consumer->newTopic('test');
        $partition = 0; // Topic của bạn có 1 partition, nên partition là 0
        $topic->consumeStart($partition, RD_KAFKA_OFFSET_BEGINNING); // Bắt đầu từ đầu topic

        $messages = [];
        $maxMessages = 10;
        $counter  = 0;

        while ($counter < $maxMessages) {
            // Lấy message từ Kafka với timeout 2000ms (2 giây)
            $message = $topic->consume($partition, 2000); // Tăng timeout lên 2 giây

            if ($message === null) {
                break; // Nếu không có message trong timeout, thoát vòng lặp
            }

            // Kiểm tra xem có lỗi khi nhận message không
            if ($message->err) {
                return response()->json('Error consuming message: ' . $message->errstr(), 500);
            }

            // Lưu thông tin message
            $messages[] = [
                'topic' => $message->topic_name,
                'partition' => $message->partition,
                'offset' => $message->offset,
                'message' => $message->payload
            ];
//            $topic->commit($message);
            $counter++;
        }

        return response()->json($messages);
    } catch (\Exception $e) {
        return response()->json("Error consuming message: " . $e->getMessage(), 500);
    }
});

Route::get('/consume-data', function () {
    if (!extension_loaded('rdkafka')) {
        return response()->json('rdkafka extension is not installed.', 500);
    }

    try {
        // Cấu hình Kafka Consumer
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', '171.244.129.211:31003'); // Đảm bảo broker IP đúng
//        $conf->set('group.id', 'test-consumer-group');  // Đặt tên cho consumer group của bạn
//        $conf->set('enable.auto.commit', 'true'); // Tự động commit offset sau khi nhận dữ liệu
        $conf->set('group.id', 'ai_send_data_to_web_29');  // Đặt tên cho consumer group của bạn
        $conf->set('enable.auto.commit', 'true'); // Tự động commit offset sau khi nhận dữ liệu

        // Khởi tạo Kafka Consumer
        $consumer = new RdKafka\Consumer($conf);

        // Đăng ký topic và partition
        $topic = $consumer->newTopic('ai_send_data_to_web');
        $partition = 0; // Topic của bạn có 1 partition, nên partition là 0
        $topic->consumeStart($partition, RD_KAFKA_OFFSET_BEGINNING); // Bắt đầu từ đầu topic

        $messages = [];
        $maxMessages = 10;
        $counter  = 0;

        while ($counter < $maxMessages) {
            // Lấy message từ Kafka với timeout 2000ms (2 giây)
            $message = $topic->consume($partition, 2000); // Tăng timeout lên 2 giây
            dump($message);
            if ($message === null) {
                break; // Nếu không có message trong timeout, thoát vòng lặp
            }

            // Kiểm tra xem có lỗi khi nhận message không
            if ($message->err) {
                return response()->json('Error consuming message: ' . $message->errstr(), 500);
            }

            // Lưu thông tin message
            $messages[] = [
                'topic' => $message->topic_name,
                'partition' => $message->partition,
                'offset' => $message->offset,
                'message' => $message->payload
            ];
//            $topic->commit($message);
            $counter++;
        }

//        return response()->json($messages);
    } catch (\Exception $e) {
        return response()->json("Error consuming message: " . $e->getMessage(), 500);
    }
});

