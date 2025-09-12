<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Bonus;
use App\Entity\Goal;
use App\Entity\Test;
use App\Repository\BonusRepository;
use App\Security\EmailVerifier;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private EntityManagerInterface $manager;
    private BonusRepository $bonusRepository;
    private EmailVerifier $emailVerifier;
    private UserService $userService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->bonusRepository = $this->createMock(BonusRepository::class);
        $this->emailVerifier = $this->createMock(EmailVerifier::class);
        $this->userService = new UserService($this->manager, $this->bonusRepository, $this->emailVerifier);
    }

    /* Tests the store method of the UserService class.
       Verifies that the user's password is hashed and the user entity is persisted and flushed correctly. */
    public function testStore(): void{
        $user = new AppUser();
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $user->setPassword('test');
        $passwordHasher->expects($this->once())->method('hashPassword')->with($user, 'test')
            ->willReturn('hashed_password');
        $this->manager->expects($this->once())->method('persist')->with($user);
        $this->manager->expects($this->once())->method('flush');
        $this->userService->store($user, $passwordHasher);
        $this->assertSame('hashed_password', $user->getPassword());
    }

    /* Test the edit method of the UserService. This test ensures that the user's email, password, login,
       and verification status are correctly updated when the `edit` method is called with new data from a mock form.
       It checks that the password is hashed, the email is updated with the new value and the verification is set
       to false, the login is updated, and the confirmation mail is send.
     */
    public function testEdit(): void{
        $user = new AppUser();
        $form = $this->createMock(FormInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $form->method('get')
            ->willReturnMap([
                ['email', $this->createMock(FormInterface::class)],
                ['password', $this->createMock(FormInterface::class)],
                ['login', $this->createMock(FormInterface::class)],
                ['new_verification', $this->createMock(FormInterface::class)],
            ]);
        $form->get('email')->method('getData')->willReturn('new_email@example.com');
        $form->get('password')->method('getData')->willReturn('new_password');
        $form->get('login')->method('getData')->willReturn('new_login');
        $form->get('new_verification')->method('getData')->willReturn(true);
        $passwordHasher->expects($this->once())->method('hashPassword')->with($user, 'new_password')
            ->willReturn('hashed_password');
        $this->emailVerifier->expects($this->once())
            ->method('sendEmailConfirmation');
        $this->userService->edit($user, $form, $passwordHasher);
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertSame('new_email@example.com', $user->getEmail());
        $this->assertSame('new_login', $user->getLogin());
        $this->assertSame(false, $user->isVerified());
    }

    /* Tests the updateUser method of UserService.
       Ensures that the user is persisted and flushed. */
    public function testUpdateUser(): void{
        $user = $this->createMock(AppUser::class);
        $this->manager->expects($this->once())->method('persist')->with($user);
        $this->manager->expects($this->once())->method('flush');
        $this->userService->updateUser($user);
    }

    // Tests the setLastActive method to ensure correct streak calculation.
    public function testSetLastActive(): void{
        $user = new AppUser();
        $now  = new \DateTimeImmutable();
        $this->userService->setLastActive($user, $now);
        $this->assertEquals($now, $user->getLastActive());
        $this->assertEquals(1, $user->getDaysWithoutBreak());
        $this->userService->setLastActive($user, $now);
        $this->assertEquals(1, $user->getDaysWithoutBreak());
        $user->setLastActive(new \DateTimeImmutable('-1 day'));
        $this->userService->setLastActive($user, $now);
        $this->assertEquals(2, $user->getDaysWithoutBreak());
        $user->setLastActive(new \DateTimeImmutable('-2 day'));
        $this->userService->setLastActive($user, $now);
        $this->assertEquals(1, $user->getDaysWithoutBreak());
        $user->setLastActive(new \DateTimeImmutable('-2 day'));
        $bonus = new Bonus();
        $bonus->setIsUsed(false);
        $this->bonusRepository->method('getCountNotUsedDayBonuses')->with($user)
            ->willReturn(1);
        $this->bonusRepository->method('findBy')->with(
            ['owner' => $user,
                'is_used' => false,
                'type' => 'miss_day'], null, 1)->willReturn([$bonus]);
        $this->userService->setLastActive($user, $now);
        $this->assertEquals(3, $user->getDaysWithoutBreak());
        $this->assertEquals(true, $bonus->isUsed());
    }

    /* Tests the setGoal method by verifying that a new goal is correctly initialized with the given user as owner,
       target values setted to 0, status set to current, not completed, and the end date set to 7 days from now. */
    public function testSetGoal(): void{
        $user = $this->createMock(AppUser::class);
        $goal = new Goal();
        $goal->setTargetCards(1);
        $goal->setTargetTests(1);
        $this->userService->setGoal($user, $goal);
        $this->assertEquals($user, $goal->getOwner());
        $this->assertEquals(0, $goal->getAchievedCards());
        $this->assertEquals(0, $goal->getAchievedTests());
        $this->assertEquals(true, $goal->isCurrent());
        $this->assertEquals(false, $goal->isCompleted());
        $this->assertEquals(0, $goal->getEndDate()->diff(new \DateTimeImmutable('+7 days'))->days);
    }

    /* Tests the updateGoal method of UserService.
       Ensures that the goal is persisted and flushed. */
    public function testUpdateGoal(): void{
        $goal = $this->createMock(Goal::class);
        $this->manager->expects($this->once())->method('persist')->with($goal);
        $this->manager->expects($this->once())->method('flush');
        $this->userService->updateGoal($goal);
    }

    /* Tests the completeGoal method to ensure that upon goal completion, the end date is updated,
       bonuses are granted, status is changed to not current, and a success notification is triggered. */
    public function testCompleteGoal(): void{
        $goal = $this->createMock(Goal::class);
        $user = $this->createMock(AppUser::class);
        $goal->method('getOwner')->willReturn($user);
        $goal->method('getTargetCards')->willReturn(1);
        $goal->method('getTargetTests')->willReturn(1);
        $goal->expects($this->once())->method('setEndDate')
            ->with($this->isInstanceOf(\DateTimeImmutable::class));
        $goal->expects($this->once())->method('setBonusGranted')->with(true);
        $goal->expects($this->once())->method('setIsCurrent')->with(false);
        $service = $this->getMockBuilder(UserService::class)
        ->disableOriginalConstructor()
            ->onlyMethods(['addDayBonus', 'addTestBonus', 'updateGoal', 'createSuccessfullNotification'])
            ->getMock();
        $service->expects($this->once())->method('addDayBonus')->with($user);
        $service->expects($this->once())->method('addTestBonus')->with($user);
        $service->expects($this->once())->method('createSuccessfullNotification')->with($user, $goal);
        $service->completeGoal($goal);
    }

    /* Tests the addition of day and test bonuses for a user.
       This test ensures that the correct bonus types ("miss_day" and "successful_test") are created and associated
       with the given user. Verifies that the bonus type, usage status, and owner of the bonuses are correctly set. */
    public function testAddTest(): void{
        $user = $this->createMock(AppUser::class);
        $dayBonus = $this->userService->addDayBonus($user);
        $this->assertEquals('miss_day', $dayBonus->getType());
        $this->assertEquals(false, $dayBonus->isUsed());
        $this->assertEquals($user, $dayBonus->getOwner());
        $testBonus = $this->userService->addTestBonus($user);
        $this->assertEquals('successful_test', $testBonus->getType());
        $this->assertEquals(false, $testBonus->isUsed());
        $this->assertEquals($user, $testBonus->getOwner());
    }

    /* Tests the creation of different types of notifications for a user.
       Verifies that the `createExpiredGoalNotification`, `createSuccessfullNotification`, and
       `createAddDayBonusNotification` methods correctly assign the notifications to the specified user.
       Also tests that method readNotification correctly sets notification as readed. */
    public function testNotifications(): void{
        $user = $this->createMock(AppUser::class);
        $now = new \DateTimeImmutable();
        $goal = $this->createMock(Goal::class);
        $goal->method('getEndDate')->willReturn($now);
        $goal->method('getStartDate')->willReturn($now);
        $user->method('getDaysWithoutBreak')->willReturn(5);
        $expiredNtf = $this->userService->createExpiredGoalNotification($user, $goal);
        $successNtf = $this->userService->createSuccessfullNotification($user, $goal);
        $bonusNtf = $this->userService->createAddDayBonusNotification($user);
        $this->assertEquals($user, $expiredNtf->getPersonToNotificate());
        $this->assertEquals(false, $expiredNtf->isRead());
        $this->assertEquals($user, $successNtf->getPersonToNotificate());
        $this->assertEquals($user, $bonusNtf->getPersonToNotificate());
        $this->userService->readNotification($expiredNtf);
        $this->assertEquals(true, $expiredNtf->isRead());
    }

    // Tests that day and test bonuses are correctly marked as used.
    public function testRemoveBonuses(): void{
        $bonus = new Bonus();
        $user = $this->createMock(AppUser::class);
        $bonus->setIsUsed(false);
        $this->bonusRepository->method('findBy')
            ->willReturnMap([
            [
                ['owner' => $user, 'is_used' => false, 'type' => 'miss_day'], null, 1, null,
                [$bonus]
            ],
            [
                ['owner' => $user, 'is_used' => false, 'type' => 'successful_test'], null, 1, null,
                [$bonus]
            ],
        ]);
        $this->userService->removeDayBonus($user);
        $this->assertEquals(true, $bonus->isUsed());
        $bonus->setIsUsed(false);
        $this->userService->removeTestBonus($user);
        $this->assertEquals(true, $bonus->isUsed());
    }

    // Tests that learning a card correctly increments the achieved cards count in the user's active goal.
    public function testLearnCard(): void{
        $user = $this->createMock(AppUser::class);
        $goal = new Goal();
        $goal->setTargetCards(5);
        $goal->setAchievedCards(0);
        $user->method('getActualGoal')->willReturn($goal);
        $this->userService->learnCard($user);
        $this->assertEquals(1, $goal->getAchievedCards());
        $goal->setTargetCards(null);
        $goal->setAchievedCards(null);
        $this->userService->learnCard($user);
        $this->assertEquals(null, $goal->getAchievedCards());
    }

    /* Tests the finishTest method to ensure the user's goal is updated correctly based on the number of correct answers.
       Verifies that when the user answers enough questions correctly or has available test bonuses,
       the achieved test count is increased. Also checks behavior when the goal has no target set for tests. */
    public function testFinishTest(): void{
        $user = $this->createMock(AppUser::class);
        $test = $this->createMock(Test::class);
        $test->method('getNumberOfQuestions')->willReturn(1);
        $goal = new Goal();
        $goal->setTargetTests(5);
        $goal->setAchievedTests(0);
        $user->method('getActualGoal')->willReturn($goal);
        $this->bonusRepository->method('getCountNotUsedTestBonuses')->willReturn(1);
        $this->userService->finishTest($user, $test, 1);
        $this->assertEquals(1, $goal->getAchievedTests());
        $goal->setAchievedTests(0);
        $this->userService->finishTest($user, $test, 0);
        $this->assertEquals(1, $goal->getAchievedTests());
        $goal->setTargetTests(null);
        $goal->setAchievedTests(null);
        $this->userService->finishTest($user, $test, 1);
        $this->assertEquals(null, $goal->getAchievedTests());
    }

    // Tests the sendVerificationEmail method to ensure that the email confirmation is triggered for the specified user.
    public function testSendVerificationEmail(): void{
        $user = $this->createMock(AppUser::class);
        $user->method('getEmail')->willReturn('test@example.com');
        $this->emailVerifier->expects($this->once())->method('sendEmailConfirmation');
        $this->userService->sendVerificationEmail($user);
    }
}
