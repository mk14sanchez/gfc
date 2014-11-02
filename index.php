<?php
/**
 * Constraints:
 *  1. The current database designed is convoluted. DB Designer should consult
 *      a more experienced one..
 *  2. Time. Too limited time... No error handling, no code organization.
 */

$f3=require('lib/base.php');

$f3->set('DEBUG',3);
if ((float)PCRE_VERSION<7.9)
trigger_error('PCRE version is out of date');

$f3->config('config.ini');

$f3->route('GET @home: /', function($f3){
    if ($f3->exists('SESSION.user')) {
        $f3->reroute('/loan');
    }
    echo Template::instance()->render('home.html');

});

$f3->route('GET|POST @login: /login','AuthController->login');
$f3->route('GET @logout: /logout','AuthController->logout');
$f3->route('GET|POST @loans_list: /loan', 'LoanController->display_pending');
$f3->route('GET @loans_list_approved: /loan/approved', 'LoanController->display_approved');
$f3->route('GET @loans_list_rejected: /loan/rejected', 'LoanController->display_rejected');
$f3->route('GET|POST @loans_apply: /loan/apply', 'LoanController->apply');
$f3->route('POST @loans_payment: /loan/payment', 'LoanController->compute_payment');
$f3->route('GET|POST @register: /register', 'RegisterController->register');
$f3->route('GET|POST @profile: /profile', 'UserController->profile');
$f3->route('GET @user_profile: /user_profile', 'UserController->user_profile');
$f3->route('GET|POST @search: /search', 'UserController->search');

$f3->set('DB',
    new DB\SQL(
        'mysql:host=127.0.0.1;port=3306;dbname=gfc',
        'root',
        ''
    )
);

class CarBrand extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'brand');
    }
}

class CarType extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'type');
    }
}

class CarStatus extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'car_status');
    }
}

class LoansDetail extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'loan_details');
    }

    public function get_user_loans($filter=NULL){

        $sql = 'SELECT *, e.name as brand_name, f.name as car_status FROM loan_details as a LEFT JOIN loan as b ON
            a.loan_no=b.loan_no LEFT JOIN car as c ON c.car_id=a.car_id
            LEFT JOIN type as d ON d.type_no=c.type_no LEFT JOIN brand as e
            ON e.brand_code=c.brand_code LEFT JOIN car_status as f
            ON f.id=c.status_id LEFT JOIN borrower as g ON g.id=b.borrower_id LEFT JOIN user as h ON g.user_email=h.email';

        if ($filter) {
            $sql .= ' WHERE ' . $filter . ';';
        }

        return $this->db->exec($sql);
    }
}

class Car extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'car');
    }
}

class Loan extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'loan');
    }
}

class User extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'user');
    }

    public function get_user($email){
        $user = $this->db->exec('SELECT *, user_type.name as user_type FROM user
        LEFT JOIN user_type ON user.user_type_code=user_type.id LEFT JOIN borrower
        ON borrower.user_email=email WHERE user.email="' . $email . '";');
        $arr_count = count($user);
        if ($arr_count == 1) {
            return $user[0];
        }
        elseif ($arr_count > 1){
            // We have a very bad error here.
            //TODO: Create error handling
            return NULL;
        }
        else {
            return NULL;
        }
    }

}

class Borrower extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'borrower');
    }
    public function get_search_results($query) {
        return $this->find("firstname LIKE '%$query%' OR lastname LIKE '%$query%'");
    }

    public function get_info($email) {
        $user = $this->db->exec('SELECT *, c.date_of_birth AS spouse_birthdate,
        d.source_of_income AS spouse_source_income, d.name_of_firm AS spouse_firm,
        d.monthly_income AS spouse_monthly_income,
        d.other_income AS spouse_other_income, d.position AS spouse_position,
        d.length_of_employment AS spouse_len_of_employment,
        d.other_source_of_income AS spouse_other_income_source FROM user AS a
        LEFT JOIN borrower AS e ON a.email=e.user_email LEFT JOIN income as b
        ON b.borrower_id = e.id LEFT JOIN spouse
        AS c ON b.spouse_id=c.id LEFT JOIN spouse_income as d ON
        b.spouse_id=d.spouse_id  WHERE e.user_email="' . $email . '";');

        $arr_count = count($user);
        if ($arr_count == 1) {
            return $user[0];
        }
        elseif ($arr_count > 1){
            // We have a very bad error here.
            //TODO: Create error handling
            return NULL;
        }
        else {
            return NULL;
        }
    }

    public function get_IDs($email) {
     //SELECT a.id AS borrower_id, b.id AS spouse_id, c.income_code AS spouse_income_id, d.id AS income_id FROM borrower AS a LEFT JOIN spouse AS b ON a.id=b.borrower_id LEFT JOIN spouse_income AS c ON b.id=c.spouse_id LEFT JOIN income AS d ON a.id=d.borrower_id WHERE a.user_email='a@yahoo.com'
        $user = $this->db->exec("SELECT a.id AS borrower_id, b.id AS spouse_id,
        c.income_code AS spouse_income_id, d.id AS income_id FROM borrower AS a
        LEFT JOIN spouse AS b ON a.id=b.borrower_id LEFT JOIN spouse_income AS
        c ON b.id=c.spouse_id LEFT JOIN income AS d ON a.id=d.borrower_id WHERE
        a.user_email='$email';");

        $arr_count = count($user);
        if ($arr_count == 1) {
            return $user[0];
        }
        elseif ($arr_count > 1){
            // We have a very bad error here.
            //TODO: Create error handling
            return NULL;
        }
        else {
            return NULL;
        }
    }
}

class Spouse extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'spouse');
    }
}

class SpouseIncome extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'spouse_income');
    }
}

class Income extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'income');
    }
}

class UserController {
    public function profile($f3) {
        if ($f3->exists('SESSION.user')) {

            $user_arr = $f3->get("SESSION.user");
            $user_email = $user_arr["email"];

            $post_data = $f3->get("POST");
            if ($post_data) {
                if ($post_data['email']) {
                    if ($post_data["password"] == $post_data["password2"]) {

                        $db = $f3->get('DB');
                        $db->begin();

                        $user = new User();

                        $user = $user->load(array("email=\"$user_email\""));
                        $user->username = $post_data["username"];
                        $user->password = $post_data["password"];
                        $user->user_type = 1;
                        $user->update();

                        $user_arr = $user->get_user($user_email);

                        new \Session();
                        $f3->set('SESSION.user', $user_arr);

                        $borrower = new Borrower();
                        $borrower_data = $borrower->get_IDs($user_email);

                        $borrower_id = $borrower_data['borrower_id'];
                        $spouse_id = $borrower_data['spouse_id'];

                        $borrower = $borrower->load(array("id=$borrower_id"));
                        $borrower->lastname = $post_data["last_name"];
                        $borrower->firstname = $post_data["first_name"];
                        $borrower->middlename = $post_data["middle_name"];
                        $borrower->address = $post_data["user_address"];
                        $borrower->contact_no = $post_data["user_tel_no"];
                        $borrower->year_in_address = $post_data["year_address"];
                        $borrower->birthdate = $post_data["birth_date"];
                        $borrower->no_of_dependencies = $post_data["number_of_dependents"];
                        $borrower->marital_status = $post_data["marital_status"];
                        $borrower->citizenship = $post_data["citizenship"];
                        $borrower->update();

                        if ($spouse_id) {
                            $spouse = new Spouse();
                            $spouse = $spouse->load(array("borrower_id=$borrower_id"));
                            $spouse->spousename = $post_data["spouse_name"];
                            $spouse->date_of_birth = $post_data["spouse_birthdate"];
                            $spouse->update();

                            $spouse_income = new SpouseIncome();
                            $spouse_income->load(array("spouse_id=$spouse_id"));
                            $spouse_income->source_of_income = $post_data["spouse_income_source"];
                            $spouse_income->name_of_firm = $post_data["spouse_firm"];
                            $spouse_income->monthly_income= $post_data["spouse_monthly_income"];
                            $spouse_income->other_income = $post_data["spouse_other_income"];
                            $spouse_income->position = $post_data["spouse_position"];
                            $spouse_income->other_source_of_income = $post_data["spouse_other_source_of_income"];
                            $spouse_income->spouse_address = $post_data["spouse_address"];
                            $spouse_income->spouse_tel_no = $post_data["spouse_tel_no"];
                            $spouse_income->length_of_employment = $post_data["spouse_len_of_employment"];
                            $spouse_income->update();
                        } else {
                            if ($post_data["spouse_name"]) {
                                $spouse = new Spouse();
                                $spouse = $spouse->borrower_id = $borrower_id;
                                $spouse->spousename = $post_data["spouse_name"];
                                $spouse->date_of_birth = $post_data["spouse_birthdate"];
                                $spouse->save();

                                $spouse_id = $spouse->get('_id');

                                $spouse_income = new SpouseIncome();
                                $spouse_income->spouse_id = $spouse_id;
                                $spouse_income->source_of_income = $post_data["spouse_income_source"];
                                $spouse_income->name_of_firm = $post_data["spouse_firm"];
                                $spouse_income->monthly_income= $post_data["spouse_monthly_income"];
                                $spouse_income->other_income = $post_data["spouse_other_income"];
                                $spouse_income->position = $post_data["spouse_position"];
                                $spouse_income->other_source_of_income = $post_data["spouse_other_source_of_income"];
                                $spouse_income->spouse_address = $post_data["spouse_address"];
                                $spouse_income->spouse_tel_no = $post_data["spouse_tel_no"];
                                $spouse_income->length_of_employment = $post_data["spouse_len_of_employment"];
                                $spouse_income->save();                            }
                        }

                        $income = new Income();
                        $income->reset();
                        $income->load(array("spouse_id=$spouse_id"));
                        $income->borrower_id = $borrower_id;
                        $income->source_income = $post_data["income_source"];
                        $income->length_of_employment = $post_data["len_of_employment"];
                        $income->name_of_firm = $post_data["name_of_firm"];
                        $income->position = $post_data["position"];
                        $income->firm_address = $post_data["firm_address"];
                        $income->previous_employment = $post_data["previous_employment"];
                        $income->previous_employment_address = $post_data["previous_employment_address"];
                        $income->previous_employment_tel_no = $post_data["previous_employment_tel_no"];
                        $income->monthly_income = $post_data["monthly_income"];
                        $income->other_income_source = $post_data["other_income_source"];
                        $income->update();
                        $db->commit();

                        $profile_update_msg = "Account successfully updated.";
                        $f3->set("register_msg", $profile_update_msg);
                        $f3->set('profile_update_msg', $profile_update_msg);
                    } else {
                        $profile_update_msg = "Passwords does not match.";
                    }
                }
                else {
                    $profile_update_msg = "Invalid email address.";
                }
            }
            else {
                $profile_update_msg = "";
                $borrower = new Borrower();

                $f3->set('user', $user_arr);
            }
        }
        $f3->set('profile_update_msg', $profile_update_msg);
        $f3->set('user', $user_arr);
        $borrower_data = $borrower->get_info($user_email);

        $f3->set('borrower', $borrower_data);
        echo Template::instance()->render('profile.html');
    }
    public function search($f3) {
        if ($f3->exists('SESSION.user')) {
            $post_data = $f3->get("POST");
            $users = new Borrower();
            if ($post_data) {
                if (isset($post_data["delete"])) {
                    $user = new User();
                    $email = $post_data["delete"];
                    $user->erase(array("email='$email'"));
                    $results =$users->find();
                } else {
                    $results = $users->get_search_results($post_data["query"]);
                }
            } else {
                $results =$users->find();
            }
            $f3->set("users", $results);
            echo Template::instance()->render('search.html');
        } else {
            $f3->reroute("/login");
        }
    }
    public function user_profile($f3) {

        if ($f3->exists('SESSION.user')) {

            if ($f3->get("GET"))
            {
                $get_data = $f3->get("GET");
                $user_email = $get_data["email"];

                $u = new User();
                $borrower = new Borrower();
                $borrower_data = $borrower->get_info($user_email);
                $user = $u->load("email='$user_email'");
            } else {
                $borrower_data = NULL;
                $user = NULL;
            }
            $f3->set('user', $user);
            $f3->set('borrower', $borrower_data);
            echo Template::instance()->render('user_profile.html');
        }
    }
}


class RegisterController {
    public function register($f3) {
        $register_error = "";

        if ($f3->get('POST')) {

            $spouse_id = NULL;

            $post_data = $f3->get('POST');

            $register_error = "Email is missing.";

            if ($post_data['email']) {
                $register_error = "Passwords does not match.";
                if ($post_data["password"] == $post_data["password2"]) {
                    $register_error = "";

                    $db = $f3->get('DB');
                    $db->begin();

                    $user = new User();
                    $user->email = $post_data["email"];
                    $user->username = $post_data["username"];
                    $user->password = $post_data["password"];
                    $user->user_type = 1;
                    $user->save();

                    $borrower = new Borrower();
                    $borrower->user_email = $post_data["email"];
                    $borrower->lastname = $post_data["last_name"];
                    $borrower->firstname = $post_data["first_name"];
                    $borrower->middlename = $post_data["middle_name"];
                    $borrower->address = $post_data["user_address"];
                    $borrower->contact_no = $post_data["user_tel_no"];
                    $borrower->year_in_address = $post_data["year_address"];
                    $borrower->birthdate = $post_data["birth_date"];
                    $borrower->no_of_dependencies = $post_data["number_of_dependents"];
                    $borrower->marital_status = $post_data["marital_status"];
                    $borrower->citizenship = $post_data["citizenship"];
                    $borrower->save();

                    $borrower_id = $borrower->get('_id');

                    if ($post_data['spouse_name']) {
                        $spouse = new Spouse();
                        $spouse->borrower_id = $borrower_id;
                        $spouse->spousename = $post_data["spouse_name"];
                        $spouse->date_of_birth = $post_data["spouse_birthdate"];
                        $spouse->save();

                        $spouse_id = $spouse->get('_id');

                        $spouse_income = new SpouseIncome();
                        $spouse_income->spouse_id = $spouse_id;
                        $spouse_income->source_of_income = $post_data["spouse_income_source"];
                        $spouse_income->name_of_firm = $post_data["spouse_firm"];
                        $spouse_income->monthly_income= $post_data["spouse_monthly_income"];
                        $spouse_income->other_income = $post_data["spouse_other_income"];
                        $spouse_income->position = $post_data["spouse_position"];
                        $spouse_income->other_source_of_income = $post_data["spouse_other_source_of_income"];
                        $spouse_income->spouse_address = $post_data["spouse_address"];
                        $spouse_income->spouse_tel_no = $post_data["spouse_tel_no"];
                        $spouse_income->length_of_employment = $post_data["spouse_len_of_employment"];
                        $spouse_income->save();
                    }

                    $income = new Income();
                    $income->spouse_id = $spouse_id;
                    $income->borrower_id = $borrower_id;
                    $income->source_income = $post_data["income_source"];
                    $income->length_of_employment = $post_data["len_of_employment"];
                    $income->name_of_firm = $post_data["name_of_firm"];
                    $income->position = $post_data["position"];
                    $income->firm_address = $post_data["firm_address"];
                    $income->previous_employment = $post_data["previous_employment"];
                    $income->previous_employment_address = $post_data["previous_employment_address"];
                    $income->previous_employment_tel_no = $post_data["previous_employment_tel_no"];
                    $income->monthly_income = $post_data["monthly_income"];
                    $income->other_income_source = $post_data["other_income_source"];
                    $income->save();
                    $db->commit();
                    $f3->set("login_error", "Account successfully created.");
                    $f3->reroute('/login');
                }
            }
        }
        $f3->set('register_error', $register_error);
        echo Template::instance()->render('register.html');
    }
}

class AuthController {
    public function login($f3){
        if ($f3->exists('SESSION.user')) {
            $f3->reroute('/loan');
        }

        $login_error = '';
        if ($f3->get('POST')) {
            $user = new User();
            $auth = new \Auth($user, array('id'=>'email', 'pw'=>'password'));

            $post_data = $f3->get('POST');
            $email = $post_data['email'];
            $pw = $post_data['password'];

            if ($auth->login($email, $pw)){
                $u = $user->get_user($email);

                if ($u) {
                    new \Session();
                    $f3->set('SESSION.user', $u);
                    $f3->reroute('/loan');
                }
                $login_error = 'Critical error! Contanct administrator.';
            } else {
                $login_error = "Invalid username or password";
            }
        }
        $f3->set('login_error', $login_error);
        echo Template::instance()->render("login.html");
    }

    public function logout($f3) {
        $f3->clear('SESSION');
        $f3->reroute('/');
    }
}

class LoanController {
    public function compute_payment($f3) {
        if (!$f3->exists('SESSION.user')) {
            $f3->reroute('/');
        }

        if ($f3->exists('POST')) {
            $post_data = $f3->get('POST');
            $loan_no = $post_data['loan_id'];

            $loan = new Loan();
            $result = $loan->load(array("loan_no=$loan_no"));

            $INTEREST = .10; //This interest is hardcoded.

            $amt = $result->amount_financed;
            $total = $amt * $INTEREST + $amt;
            $term = $result->term;
            $montly_due = round($total/$term, 2);
            $payment = array(
                'amount' => $amt,
                'interest' => '10 %',
                'total' => $total,
                'monthly_due' => $montly_due,
                'term'=> $term
            );

            $f3->set('payment', $payment);
        }

        echo Template::instance()->render('payment.html');
    }

    public function display_approved($f3) {
        $user = $f3->get('SESSION.user');
        if (!$user) {
            $f3->reroute('/');
        }

        $user_type = $user['user_type'];

        $filter_id = '';
        if ($user_type == 'user') {
            $filter_id = " AND g.id=". $user['id'];
            $template = 'loan.html';
        } else {
            $template = 'admin.html';
        }

        $loan = new LoansDetail();
        $filter = "status='Approved'" . $filter_id;
        $filtered_query = $loan->get_user_loans($filter);
        $f3->set('loans_results', $filtered_query);
        echo Template::instance()->render($template);
    }

    public function display_rejected($f3) {
        $user = $f3->get('SESSION.user');
        if (!$user) {
            $f3->reroute('/');
        }

        $user_type = $user['user_type'];

        $filter_id = '';
        if ($user_type == 'user') {
            $filter_id = " AND g.id=". $user['id'];
            $template = 'loan.html';
        } else {
            $template = 'admin.html';
        }

        $loan = new LoansDetail();
        $filter = "status='Rejected'" . $filter_id;
        $filtered_query = $loan->get_user_loans($filter);
        $f3->set('loans_results', $filtered_query);
        echo Template::instance()->render($template);
    }

    public function display_pending($f3) {

        $user = $f3->get('SESSION.user');
        if (!$user) {
            $f3->reroute('/');
        }

        $user_type = strtolower($user['user_type']);

        if ($f3->get('POST') && $user_type == 'admin') {
            $post_data = $f3->get('POST');

            $amt_financed = '';


            if (isset($post_data['approve'])) {
                $status = 'Approved';
                $loan_no = $post_data['approve'];
                $amt_financed = $post_data["amt_financed"."_$loan_no"];
            }
            else {
                $status = 'Rejected';
                $loan_no = $post_data['reject'];
            }
            $loan = new Loan();
            $loan->load(array("loan_no=$loan_no"));
            $loan->status=$status;
            $loan->amount_financed=$amt_financed;

            $db = $f3->get("DB");
            $db->begin();
            $loan->update();
            $db->commit();
        }

        $loan = new LoansDetail();

        $filter_id = '';
        if ($user_type == 'user') {
            $filter_id = " AND g.id=". $user['id'];
            $template = 'loan.html';
        } else {
            $template = 'admin.html';
        }

        $filter = "status='Pending'" . $filter_id;
        $filtered_query = $loan->get_user_loans($filter);
        $f3->set('loans_results', $filtered_query);
        echo Template::instance()->render($template);
    }

    public function apply($f3) {
        $user = $f3->get('SESSION.user');
        if (!$user) {
            $f3->reroute('/');
        }

        $post_data = $f3->get('POST');

        if ($post_data) {

            $db = $f3->get("DB");
            $db->begin();
            $car = new Car();

            $car->car_id=$post_data['car_id'];
            $car->year_model=$post_data['year_model'];
            $car->accessories=$post_data['accessories'];
            $car->dealer=$post_data['dealer'];
            $car->address=$post_data['dealer_address'];
            $car->brand_code=$post_data['brand'];
            $car->status_id=$post_data['car_status'];
            $car->type_no=$post_data['car_type'];
            $car->save();

            $loans = new Loan();
            $loans->borrower_id=$user['id'];
            $loans->term=$post_data['loan_term'];
            //TODO: Fix this.
            $loans->date = 'NOW()';
            $loans->save();

            $loan_details = new LoansDetail();
            $loan_details->car_id= $post_data['car_id'];
            $loan_details->loan_no = $loans->get('_id');
            $loan_details->save();
            $db->commit();

            $f3->reroute('/loan');
        }

        $car_brands = new CarBrand();
        $car_type = new CarType();
        $car_status = new CarStatus();

        $f3->set('car_brands', $car_brands->find());
        $f3->set('car_types', $car_type->find());
        $f3->set('car_stats', $car_status->find());

        echo Template::instance()->render('apply.html');
    }
}

$f3->run();
