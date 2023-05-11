<?php

use Psr\Http\Message\ResponseInterface as Response;

class Get
{
    private string $sql;
    private Response $response;
    private mixed $result;
    private mixed $error;

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
            $this->result = $statement->fetch(PDO::FETCH_OBJ);

            $db = null;
            $this->response->getBody()->write(json_encode($this->result));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $this->error = array(
                "Message" => $e->getMessage()
            );

            $this->response->getBody()->write(json_encode($this->error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    }
}