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
    $f3->reroute('/login');
});

$f3->route('GET|POST @login: /login','AuthController->login');
$f3->route('GET @logout: /logout','AuthController->logout');
$f3->route('GET @loans_list: /loan', 'LoanController->display_pending');
$f3->route('GET @loans_list_approved: /loan/approved', 'LoanController->display_approved');
$f3->route('GET @loans_list_rejected: /loan/rejected', 'LoanController->display_rejected');
$f3->route('GET|POST @loans_apply: /loan/apply', 'LoanController->apply');
$f3->route('POST @loans_payment: /loan/payment', 'LoanController->compute_payment');

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
            $total = $amt * $INTEREST;
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
            $loan->update();
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
