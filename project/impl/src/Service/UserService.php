<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Bonus;
use App\Entity\Goal;
use App\Entity\Notification;
use App\Entity\Test;
use App\Repository\BonusRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private BonusRepository $bonusRepository,
        private EmailVerifier $emailVerifier,
    ) {}

    // Hashes the user's password, persists the user entity, and flushes changes to the database.
    public function store(AppUser $user, UserPasswordHasherInterface $passwordHasher): ?int{
        $password = $user->getPassword();
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $this->manager->persist($user);
        $this->manager->flush();
        return $user->getId();
    }

    /* Edits the user details based on the submitted form data.
       Updates the user's email, login, and password if new values are provided.
       Sends a verification email if the email or verification status is updated.
       Hashes the new password before saving it.
       Updates the user's details in the database. */
    public function edit(AppUser $user, FormInterface $form, UserPasswordHasherInterface $passwordHasher): ?int{
        $new_email = $form->get('email')->getData();
        $new_password = $form->get('password')->getData();
        $new_login = $form->get('login')->getData();
        $new_verification = $form->get('new_verification')->getData();
        $to_send_mail = false;
        if ($new_email && $user->getEmail() !== $new_email){
            $user->setEmail($new_email);
            $user->setIsVerified(false);
            $to_send_mail = true;
        }
        if ($new_password){
            $hashedPassword = $passwordHasher->hashPassword($user, $new_password);
            $user->setPassword($hashedPassword);
        }
        if ($new_login)
            $user->setLogin($new_login);
        if ($new_verification)
            $to_send_mail = true;
        if ($to_send_mail)
            $this->sendVerificationEmail($user);
        $this->updateUser($user);
        return $user->getId();
    }

    // Persists the given user to the database.
    public function updateUser(AppUser $user): void
    {
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /* Updates the user's last active date and tracks their learning streak. If the user was active the previous day,
       it increments the streak counter. If the user was inactive for more than a day, it attempts to use available
       unused day bonuses to preserve the streak. If bonuses are insufficient, the streak resets. When the streak
       reaches a multiple of 5, a day bonus is added and a notification is created.
     */
    public function setLastActive(AppUser $user, \DateTimeImmutable $date): void{
        if ($user->getLastActive()){
            $nowDateOnly = $date->setTime(0, 0);
            $targetDateOnly = $user->getLastActive()->setTime(0, 0);
            $days = $nowDateOnly->diff($targetDateOnly)->days;
            if ($days === 1)
                $user->increaseDaysWithoutBreak();
            else if ($days > 1){
                $not_used = $this->bonusRepository->getCountNotUsedDayBonuses($user);
                if ($not_used >= $days-1){
                    $user->increaseDaysWithoutBreak($days);
                    $this->removeDayBonus($user, $days-1);
                }
                else
                    $user->setDaysWithoutBreak(1);
            }
        }
        else
            $user->setDaysWithoutBreak(1);
        $user->setLastActive($date);
        if ($user->getDaysWithoutBreak() % 5 === 0){
            $this->addDayBonus($user);
            $this->createAddDayBonusNotification($user);
        }
        $this->updateUser($user);
    }

    /* Sets a new goal for the user by assigning the user as the owner,
       initializing the start and end dates with a 7-day duration, setting achieved progress to 0 if targets are set,
       marking it as the current goal, updating it in the database, and returning the goal ID.
     */
    public function setGoal(AppUser $user, Goal $goal): ?int{
        $time = new \DateTimeImmutable();
        $goal->setOwner($user);
        $goal->setStartDate($time);
        $goal->setEndDate($time->modify('+7 days'));
        if ($goal->getTargetCards())
            $goal->setAchievedCards(0);
        if ($goal->getTargetTests())
            $goal->setAchievedTests(0);
        $goal->setIsCurrent(true);
        $this->updateGoal($goal);
        return $goal->getId();
    }

    // Persists the given goal to the database.
    public function updateGoal(Goal $goal): void{
        $this->manager->persist($goal);
        $this->manager->flush();
    }

    /* Completes the given goal by setting the end date to the current time,
       granting appropriate bonuses based on the goal's targets, marking the goal as no longer current and
       bonuses as granted, updating it in the database, and creating a success notification for the user. */
    public function completeGoal(Goal $goal): void{
        $time = new \DateTimeImmutable();
        $goal->setEndDate($time);
        $user = $goal->getOwner();
        if ($goal->getTargetCards())
            $this->addDayBonus($user);
        if ($goal->getTargetTests())
            $this->addTestBonus($user);
        $goal->setBonusGranted(true);
        $goal->setIsCurrent(false);
        $this->updateGoal($goal);
        $this->createSuccessfullNotification($user, $goal);
    }

    /* Adds a "miss_day" bonus for the given user. It creates a new Bonus object, sets the necessary properties,
       and persists it to the database. */
    public function addDayBonus(AppUser $user): Bonus {
        $bonus = new Bonus();
        $bonus->setType('miss_day');
        $bonus->setOwner($user);
        $bonus->setGrantedAt(new \DateTimeImmutable());
        $this->manager->persist($bonus);
        $this->manager->flush();
        return $bonus;
    }

    /* Adds a "successful_test" bonus for the given user. It creates a new Bonus object, sets the necessary properties,
       and persists it to the database. */
    public function addTestBonus(AppUser $user): Bonus {
        $bonus = new Bonus();
        $bonus->setType('successful_test');
        $bonus->setOwner($user);
        $bonus->setGrantedAt(new \DateTimeImmutable());
        $this->manager->persist($bonus);
        $this->manager->flush();
        return $bonus;
    }

    /* Creates a notification for an expired goal.
       Generates a notification informing the user that the goal was not achieved before the specified end date.
       The notification message includes the goal's end date, and the notification is assigned to the given user.
       Persists and flushes the notification into the database. */
    public function createExpiredGoalNotification(AppUser $user, Goal $goal): Notification{
        $notification = new Notification();
        $endDate = $goal->getEndDate()->format('d.m.Y');
        $notification->setMessage("Cíl nebyl dosažen. Termín splnění byl do {$endDate}.");
        $notification->setPersonToNotificate($user);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($notification);
        $this->manager->flush();
        return $notification;
    }

    /* Creates a notification for a successfully completed goal.
       Generates a notification informing the user that the goal was successfully completed.
       The notification message includes the goal's start date, and the notification is assigned to the given user.
       Persists and flushes the notification into the database. */
    public function createSuccessfullNotification(AppUser $user, Goal $goal): Notification{
        $notification = new Notification();
        $startDate = $goal->getStartDate()->format('d.m.Y');
        $notification->setMessage("Cíl zahájený dne {$startDate} byl dosažen");
        $notification->setPersonToNotificate($user);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($notification);
        $this->manager->flush();
        return $notification;
    }

    /* Creates a notification for a user who has been studying continuously.
       Generates a notification informing the user that they have been studying without a break for a certain number
       of days. Congratulates the user for their persistence and notifies them of the reward – a day off from studying.
       Is assigned to the given user and is saved to the database. */
    public function createAddDayBonusNotification(AppUser $user): Notification{
        $notification = new Notification();
        $notification->setMessage("Učíte se nepřetržitě už {$user->getDaysWithoutBreak()} dny. Za to získáváte bonus – den pauzy v učení.");
        $notification->setPersonToNotificate($user);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($notification);
        $this->manager->flush();
        return $notification;
    }

    // Marks a given notification as read.
    public function readNotification(Notification $notification): void{
        $notification->setIsRead(true);
        $this->manager->persist($notification);
        $this->manager->flush();
    }

    /* Retrieves up to `$count` unused "miss_day" bonuses from the bonus repository
       for the specified user, marks each as used, and persists the changes to the database. */
    public function removeDayBonus(AppUser $user, int $count = 1): void{
        $bonuses = $this->bonusRepository->findBy([
            'owner' => $user,
            'is_used' => false,
            'type' => 'miss_day'], null, $count);
        foreach($bonuses as $bonus){
            $bonus->setIsUsed(true);
            $this->manager->persist($bonus);
        }
        $this->manager->flush();
    }

    /* Retrieves up to `$count` unused "successful_test" bonuses from the bonus repository
       for the specified user, marks each as used, and persists the changes to the database. */
    public function removeTestBonus(AppUser $user, int $count = 1): void{
        $bonuses = $this->bonusRepository->findBy([
            'owner' => $user,
            'is_used' => false,
            'type' => 'successful_test'], null, $count);
        foreach($bonuses as $bonus){
            $bonus->setIsUsed(true);
            $this->manager->persist($bonus);
        }
        $this->manager->flush();
    }

    // Updates the user's goal progress when a card is learned.
    public function learnCard(AppUser $user): void{
        $goal = $user->getActualGoal();
        if ($goal->getTargetCards())
        {
            $goal->increaseAchievedCards();
            $this->updateGoal($goal);
        }
    }

    /* Finalizes a test session and updates the user's goal progress accordingly.
       If the user's current goal includes a target number of tests, and the user achieves at least
       75% correct answers or has an available test bonus, this method increments the achieved tests.
       If a bonus is used due to insufficient correct answers, it is marked as used. */
    public function finishTest(AppUser $user, Test $test, int $correct_answers): void{
        $goal = $user->getActualGoal();
        if ($goal->getTargetTests()){
            $bonuses = $this->bonusRepository->getCountNotUsedTestBonuses($user);
            if ($correct_answers >= 3*$test->getNumberOfQuestions()/4 ||
                $bonuses > 0){
                if ($correct_answers < 3*$test->getNumberOfQuestions()/4){
                    $this->removeTestBonus($user);
                }
                $goal->increaseAchievedTests();
                $this->updateGoal($goal);
            }
        }
    }

    // Sends a verification email to the specified user using the email verifier service.
    public function sendVerificationEmail(AppUser $user): void{
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('mailer@wordly.com', 'Wordly'))
                ->to((string) $user->getEmail())
                ->subject('Potvrďte prosím svůj e-mail')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}