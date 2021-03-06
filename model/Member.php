<?php

require_once 'DB.php';
require_once 'Team.php';

class MemberModel
{
    public $id = null;
    public $name;
    public $password;
    public $role_id;
    public $status_id;

    const DEFAULT = USER_ID;


    public function create(): bool
    {
        $check = DB::selectOne("SELECT * FROM members WHERE name = :name", ['name' => $this->name]);

        if (!empty($check)) {
            return false;
        }

        $this->id = DB::insert("INSERT INTO members(id, name,password,role_id) VALUES (:id, :name, :password, :role_id)", ['id' => $this->id, 'name' => $this->name, 'password' => $this->name . "'s_Pa$\$w0rd", 'role_id' => $this->role_id]);

        return true;
    }

    static function make(array $params)
    {
        $member = new MemberModel();

        if (isset($params['id'])) {
            $member->id = $params['id'];
        }

        $member->name = $params['name'];
        $member->role_id = $params['role_id'];

        return $member;
    }

    static function all(): array
    {
        $res = [];

        foreach (DB::selectMany("SELECT * FROM members ORDER BY members.name", []) as $index) {
            $res[] = self::make(['id' => $index['id'], 'name' => $index['name'], 'role_id' => $index['role_id'], 'status_id' => $index['status_id']]);
        }

        return $res;
    }

    static function find(int $id): ?MemberModel
    {
        $res = DB::selectOne("SELECT * FROM members where id = :id", ['id' => $id]);

        // Si il n'y a rien, return null
        if (!isset($res[0])) {
            return null;
        }

        $res = $res[0];
        return self::make(['id' => $res['id'], 'name' => $res['name'], 'role_id' => $res['role_id'], 'status_id' => $res['status_id']]);
    }

    public function save(): bool
    {
        $check = DB::selectOne("SELECT * FROM members WHERE name = :name", ['name' => $this->name]);

        // si il n'est pas vide, alors return false, car le nom sera dupliqué
        if (!empty($check)) {
            return false;
        }

        return DB::execute("UPDATE members set name = :name, role_id = :role_id WHERE id = :id", ['id' => $this->id, 'name' => $this->name, 'role_id' => $this->role_id]);
    }

    public function delete(): bool
    {
        return self::destroy($this->id);
    }

    static function destroy(int $id): bool
    {
        try {
            DB::execute("DELETE FROM members WHERE id = :id", ['id' => $id]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function teams(): array
    {
        $res = DB::selectMany("SELECT teams.id, teams.name, state_id FROM members INNER JOIN team_member ON members.id = team_member.member_id INNER JOIN teams ON team_member.team_id = teams.id WHERE members.id = :member_id", ["member_id" => $this->id]);
        $return = [];
        foreach ($res as $result) {
            $return[] = Team::make($result);
        }
        return $return;
    }
}
