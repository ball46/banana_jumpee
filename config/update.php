<?php

use Psr\Http\Message\ResponseInterface as Response;

class Update
{
    private string $sql;
    private Response $response;
    private $result;
    private $error;

    public function __construct(string $sql, Response $response)
    {
        $this->sql = $sql;
        $this->response = $response;
    }

    public function evaluate(): void
    {
        try {
            $db = new DB();
            $conn = $db->connect();

            $statement = $conn->prepare($this->sql);
            $this->result = $statement->execute();

            $db = null;
        } catch (PDOException $e) {
            $this->error = array(
                "Message" => $e->getMessage()
            );
        }
    }

    public function return(): Response{
        try {
            $this->response->getBody()->write(json_encode($this->result));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }catch (PDOException) {
            $this->response->getBody()->write(json_encode($this->error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    }
}