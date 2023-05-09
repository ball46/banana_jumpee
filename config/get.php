<?php

use Psr\Http\Message\ResponseInterface as Response;

class Get
{
    private string $sql;
    private Response $response;

    public function __construct(string $sql, Response $response)
    {
        $this->sql = $sql;
        $this->response = $response;
    }

    public function evaluate(): Response
    {
        try {
            $db = new DB();
            $conn = $db->connect();

            $statement = $conn->query($this->sql);
            $result = $statement->fetch(PDO::FETCH_OBJ);

            $db = null;
            $this->response->getBody()->write(json_encode($result));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error = array(
                "Message" => $e->getMessage()
            );

            $this->response->getBody()->write(json_encode($error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    }
}