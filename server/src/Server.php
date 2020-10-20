<?php

use GuzzleHttp\Psr7\Request;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsConnection;

class Server implements MessageComponentInterface {
    protected \SplObjectStorage $clients;
    protected \SplObjectStorage $servers;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->servers = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface /** @var $conn WsConnection  */ $conn) {
        /** @var $request Request */
        $request = $conn->httpRequest;
        $query = $request->getUri()->getQuery();
        parse_str($query, $params);

        if (empty($params['type'])) {
            $conn->close();
            return;
        }

        if ($params['type'] == 'server') {
            $this->servers->attach($conn, ['id' => $params['id'] ?? null, 'type' => 'server']);
        }
        else {
            $this->clients->attach($conn, ['id' => null]);
        }

        echo "Connection [Open] ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface /** @var $from WsConnection  */  $from, $msg) {
        echo "Message ({$from->resourceId})\n";
        $msg = json_decode($msg, true);

        $isPrivileged = $this->servers->contains($from);

        if ($isPrivileged && isset($msg['to']) && $msg['to'] == 'server') {
            $this->processPrivilegedMessage($from, $msg);
            return;
        }

        if ($msg['subject'] == 'request-session') {
            $this->clients->offsetSet($from, $msg['info']);
            $this->broadcastServers(['subject' => 'client-connected', 'client' => $msg['info']]);
            return;
        }

        if ($isPrivileged) {
            $to = $this->findClientById($msg['to']);
        }
        else {
            $to = $this->findServerById($msg['to']);
        }

        $this->processCommonMessage($from, $to, $msg);
    }

    public function broadcastServers($data) {
        foreach ($this->servers as $server) {
            $server->send(json_encode($data));
        }
    }

    private function processCommonMessage(ConnectionInterface $from, ?ConnectionInterface $to, array $msg) {
        if ($to) {
            $to->send(json_encode($msg));
            return;
        }

        switch ($msg['subject']) {
            case 'offer':
                $from->send(json_encode([ 'type' => 'offer-rejected', 'from' => $msg['id'], 'reason' => 'Remote user not found' ]));
                break;

            case 'answer':
                $from->send(json_encode([ 'type' => 'answer-rejected', 'from' => $msg['id'], 'reason' => 'Remote user not found' ]));
                break;

            case  'ice-candidate':
                $from->send(json_encode([ 'type' => 'ice-candidate-rejected', 'from' => $msg['id'], 'reason' => 'Remote user not found' ]));
                break;

            case  'end-session':
                break;
        }
    }

    private function processPrivilegedMessage(ConnectionInterface $from, array $msg) {
        if ($msg['subject'] == 'get-active-clients') {
            $list = [];
            foreach ($this->clients as $value) {
                $info = $this->clients->getInfo();

                if ($info['id']) {
                    $list[]  = $info;
                }
            }

            $from->send(json_encode(['subject' => 'active-clients', 'clients' => $list]));
            return;
        }

        if ($msg['subject'] == 'get-client-details') {
            $to = $this->findClientById($msg['id']);
            if (!$to) {
                $from->send(json_encode([ 'subject' => 'not-found', 'from' => $msg['id'], 'reason' => 'Client not found' ]));
                return;
            }

            $info = $this->clients->offsetGet($to);
            $from->send(json_encode(['subject' => 'client-details', 'client' => $info]));
            return;
        }
    }

    public function findClientById($id): ?ConnectionInterface {
        foreach ($this->clients as$value) {
            /** @var ConnectionInterface $value */
            $info  = $this->clients->getInfo();
            if ($info['id'] == $id) {
                return $value;
            }
        }
        return null;
    }

    public function findServerById($id): ?ConnectionInterface {
        foreach ($this->servers as$value) {
            /** @var ConnectionInterface $value */
            $info  = $this->servers->getInfo();
            if ($info['id'] == $id) {
                return $value;
            }
        }
        return null;
    }

    public function onClose(ConnectionInterface /** @var $conn WsConnection  */  $conn) {
        echo "Connection [Closed] ({$conn->resourceId})\n";

        if ($this->servers->contains($conn)) {
            $this->servers->detach($conn);
            return;
        }

        $info = $this->clients->offsetGet($conn);
        if ($info['id']) {
            $this->broadcastServers(['subject' => 'client-disconnected', 'client' => $info]);
        }

        $this->clients->detach($conn);

    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $conn->close();
    }
}