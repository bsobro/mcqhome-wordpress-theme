/**
 * JavaScript tests for assessment functionality
 */

describe("Assessment JavaScript Functionality", () => {
  let assessmentModule;

  beforeEach(() => {
    // Mock assessment data
    global.mcqhome_assessment = {
      ajax_url: "/wp-admin/admin-ajax.php",
      nonce: "test-nonce",
      set_id: "123",
      user_id: "456",
      time_limit: 1800, // 30 minutes
      display_format: "next_next",
      questions: [
        {
          id: "1",
          question: "What is 2 + 2?",
          options: {
            A: "3",
            B: "4",
            C: "5",
            D: "6",
          },
        },
        {
          id: "2",
          question: "What is the capital of France?",
          options: {
            A: "London",
            B: "Berlin",
            C: "Paris",
            D: "Madrid",
          },
        },
      ],
    };

    // Create assessment HTML structure
    document.body.innerHTML = `
      <div id="mcq-assessment-container">
        <div id="mcq-timer" data-time-limit="1800">30:00</div>
        <div id="mcq-progress-bar">
          <div class="progress-fill" style="width: 0%"></div>
        </div>
        <div id="mcq-question-container">
          <div class="mcq-question" data-question-id="1">
            <h3>What is 2 + 2?</h3>
            <div class="mcq-options">
              <label><input type="radio" name="answer" value="A"> 3</label>
              <label><input type="radio" name="answer" value="B"> 4</label>
              <label><input type="radio" name="answer" value="C"> 5</label>
              <label><input type="radio" name="answer" value="D"> 6</label>
            </div>
          </div>
        </div>
        <div id="mcq-navigation">
          <button id="mcq-prev-btn" disabled>Previous</button>
          <button id="mcq-next-btn">Next</button>
          <button id="mcq-submit-btn" style="display: none;">Submit</button>
        </div>
        <div id="mcq-question-nav">
          <button class="question-nav-btn" data-question="0">1</button>
          <button class="question-nav-btn" data-question="1">2</button>
        </div>
      </div>
    `;

    // Mock the assessment module (would normally be loaded from assets/js/assessment.js)
    assessmentModule = {
      currentQuestion: 0,
      answers: {},
      timeRemaining: 1800,
      timerInterval: null,

      init: function () {
        this.bindEvents();
        this.startTimer();
        this.updateProgress();
      },

      bindEvents: function () {
        const nextBtn = document.getElementById("mcq-next-btn");
        const prevBtn = document.getElementById("mcq-prev-btn");
        const submitBtn = document.getElementById("mcq-submit-btn");

        if (nextBtn)
          nextBtn.addEventListener("click", () => this.nextQuestion());
        if (prevBtn)
          prevBtn.addEventListener("click", () => this.prevQuestion());
        if (submitBtn)
          submitBtn.addEventListener("click", () => this.submitAssessment());

        // Answer selection
        document.addEventListener("change", (e) => {
          if (e.target.name === "answer") {
            this.saveAnswer(e.target.value);
          }
        });

        // Question navigation
        document.querySelectorAll(".question-nav-btn").forEach((btn) => {
          btn.addEventListener("click", (e) => {
            const questionIndex = parseInt(e.target.dataset.question);
            this.goToQuestion(questionIndex);
          });
        });
      },

      nextQuestion: function () {
        if (
          this.currentQuestion <
          global.mcqhome_assessment.questions.length - 1
        ) {
          this.currentQuestion++;
          this.updateQuestion();
          this.updateNavigation();
          this.updateProgress();
        }
      },

      prevQuestion: function () {
        if (this.currentQuestion > 0) {
          this.currentQuestion--;
          this.updateQuestion();
          this.updateNavigation();
          this.updateProgress();
        }
      },

      goToQuestion: function (index) {
        if (index >= 0 && index < global.mcqhome_assessment.questions.length) {
          this.currentQuestion = index;
          this.updateQuestion();
          this.updateNavigation();
          this.updateProgress();
        }
      },

      saveAnswer: function (answer) {
        this.answers[this.currentQuestion] = answer;
        this.updateQuestionNavigation();
        this.autoSave();
      },

      updateQuestion: function () {
        const question =
          global.mcqhome_assessment.questions[this.currentQuestion];
        const container = document.getElementById("mcq-question-container");

        if (container && question) {
          container.innerHTML = `
            <div class="mcq-question" data-question-id="${question.id}">
              <h3>${question.question}</h3>
              <div class="mcq-options">
                ${Object.entries(question.options)
                  .map(
                    ([key, value]) => `
                  <label>
                    <input type="radio" name="answer" value="${key}" 
                           ${
                             this.answers[this.currentQuestion] === key
                               ? "checked"
                               : ""
                           }>
                    ${value}
                  </label>
                `
                  )
                  .join("")}
              </div>
            </div>
          `;
        }
      },

      updateNavigation: function () {
        const prevBtn = document.getElementById("mcq-prev-btn");
        const nextBtn = document.getElementById("mcq-next-btn");
        const submitBtn = document.getElementById("mcq-submit-btn");

        if (prevBtn) {
          prevBtn.disabled = this.currentQuestion === 0;
        }

        const isLastQuestion =
          this.currentQuestion ===
          global.mcqhome_assessment.questions.length - 1;

        if (nextBtn) {
          nextBtn.style.display = isLastQuestion ? "none" : "inline-block";
        }

        if (submitBtn) {
          submitBtn.style.display = isLastQuestion ? "inline-block" : "none";
        }
      },

      updateProgress: function () {
        const totalQuestions = global.mcqhome_assessment.questions.length;
        const answeredCount = Object.keys(this.answers).length;
        const progressPercentage = (answeredCount / totalQuestions) * 100;

        const progressFill = document.querySelector(".progress-fill");
        if (progressFill) {
          progressFill.style.width = `${progressPercentage}%`;
        }
      },

      updateQuestionNavigation: function () {
        document.querySelectorAll(".question-nav-btn").forEach((btn, index) => {
          btn.classList.remove("answered", "current");

          if (index === this.currentQuestion) {
            btn.classList.add("current");
          }

          if (this.answers.hasOwnProperty(index)) {
            btn.classList.add("answered");
          }
        });
      },

      startTimer: function () {
        if (this.timeRemaining <= 0) return;

        this.timerInterval = setInterval(() => {
          this.timeRemaining--;
          this.updateTimerDisplay();

          if (this.timeRemaining <= 0) {
            this.timeUp();
          }
        }, 1000);
      },

      updateTimerDisplay: function () {
        const timerElement = document.getElementById("mcq-timer");
        if (timerElement) {
          const minutes = Math.floor(this.timeRemaining / 60);
          const seconds = this.timeRemaining % 60;
          timerElement.textContent = `${minutes}:${seconds
            .toString()
            .padStart(2, "0")}`;

          // Add warning class when time is low
          if (this.timeRemaining <= 300) {
            // 5 minutes
            timerElement.classList.add("time-warning");
          }
        }
      },

      timeUp: function () {
        clearInterval(this.timerInterval);
        alert("Time is up! Your assessment will be submitted automatically.");
        this.submitAssessment();
      },

      autoSave: function () {
        // Mock auto-save functionality
        const saveData = {
          action: "mcqhome_save_progress",
          nonce: global.mcqhome_assessment.nonce,
          set_id: global.mcqhome_assessment.set_id,
          current_question: this.currentQuestion,
          answers: this.answers,
          progress_percentage:
            (Object.keys(this.answers).length /
              global.mcqhome_assessment.questions.length) *
            100,
        };

        // Mock AJAX call
        global.fetch(global.mcqhome_assessment.ajax_url, {
          method: "POST",
          body: new URLSearchParams(saveData),
        });
      },

      submitAssessment: function () {
        if (confirm("Are you sure you want to submit your assessment?")) {
          const submitData = {
            action: "mcqhome_submit_assessment",
            nonce: global.mcqhome_assessment.nonce,
            set_id: global.mcqhome_assessment.set_id,
            answers: this.answers,
            time_taken: 1800 - this.timeRemaining,
          };

          // Mock submission
          global
            .fetch(global.mcqhome_assessment.ajax_url, {
              method: "POST",
              body: new URLSearchParams(submitData),
            })
            .then(() => {
              window.location.href = "/assessment-results/";
            });
        }
      },
    };
  });

  afterEach(() => {
    if (assessmentModule.timerInterval) {
      clearInterval(assessmentModule.timerInterval);
    }
  });

  describe("Assessment Initialization", () => {
    test("should initialize assessment module correctly", () => {
      assessmentModule.init();

      expect(assessmentModule.currentQuestion).toBe(0);
      expect(assessmentModule.answers).toEqual({});
      expect(assessmentModule.timeRemaining).toBe(1800);
    });

    test("should bind event listeners", () => {
      const nextBtn = document.getElementById("mcq-next-btn");
      const prevBtn = document.getElementById("mcq-prev-btn");

      expect(nextBtn).toBeTruthy();
      expect(prevBtn).toBeTruthy();
      expect(prevBtn.disabled).toBe(true); // Should be disabled on first question
    });
  });

  describe("Question Navigation", () => {
    beforeEach(() => {
      assessmentModule.init();
    });

    test("should navigate to next question", () => {
      assessmentModule.nextQuestion();

      expect(assessmentModule.currentQuestion).toBe(1);
    });

    test("should navigate to previous question", () => {
      assessmentModule.currentQuestion = 1;
      assessmentModule.prevQuestion();

      expect(assessmentModule.currentQuestion).toBe(0);
    });

    test("should not go beyond question bounds", () => {
      // Try to go before first question
      assessmentModule.currentQuestion = 0;
      assessmentModule.prevQuestion();
      expect(assessmentModule.currentQuestion).toBe(0);

      // Try to go beyond last question
      assessmentModule.currentQuestion = 1;
      assessmentModule.nextQuestion();
      expect(assessmentModule.currentQuestion).toBe(1); // Should stay at last question
    });

    test("should jump to specific question", () => {
      assessmentModule.goToQuestion(1);
      expect(assessmentModule.currentQuestion).toBe(1);

      assessmentModule.goToQuestion(0);
      expect(assessmentModule.currentQuestion).toBe(0);
    });
  });

  describe("Answer Management", () => {
    beforeEach(() => {
      assessmentModule.init();
    });

    test("should save answer for current question", () => {
      assessmentModule.saveAnswer("B");

      expect(assessmentModule.answers[0]).toBe("B");
    });

    test("should update answer for same question", () => {
      assessmentModule.saveAnswer("A");
      expect(assessmentModule.answers[0]).toBe("A");

      assessmentModule.saveAnswer("C");
      expect(assessmentModule.answers[0]).toBe("C");
    });

    test("should save answers for different questions", () => {
      assessmentModule.saveAnswer("B");
      assessmentModule.nextQuestion();
      assessmentModule.saveAnswer("C");

      expect(assessmentModule.answers[0]).toBe("B");
      expect(assessmentModule.answers[1]).toBe("C");
    });
  });

  describe("Progress Tracking", () => {
    beforeEach(() => {
      assessmentModule.init();
    });

    test("should update progress based on answered questions", () => {
      const progressFill = document.querySelector(".progress-fill");

      // No answers initially
      assessmentModule.updateProgress();
      expect(progressFill.style.width).toBe("0%");

      // Answer one question (50% of 2 questions)
      assessmentModule.answers[0] = "B";
      assessmentModule.updateProgress();
      expect(progressFill.style.width).toBe("50%");

      // Answer both questions
      assessmentModule.answers[1] = "C";
      assessmentModule.updateProgress();
      expect(progressFill.style.width).toBe("100%");
    });

    test("should update question navigation indicators", () => {
      const questionBtns = document.querySelectorAll(".question-nav-btn");

      // Answer first question
      assessmentModule.answers[0] = "B";
      assessmentModule.updateQuestionNavigation();

      expect(questionBtns[0].classList.contains("answered")).toBe(true);
      expect(questionBtns[1].classList.contains("answered")).toBe(false);
    });
  });

  describe("Timer Functionality", () => {
    beforeEach(() => {
      jest.useFakeTimers();
      assessmentModule.init();
    });

    afterEach(() => {
      jest.useRealTimers();
    });

    test("should start timer and count down", () => {
      assessmentModule.timeRemaining = 10;
      assessmentModule.startTimer();

      // Fast-forward 5 seconds
      jest.advanceTimersByTime(5000);

      expect(assessmentModule.timeRemaining).toBe(5);
    });

    test("should update timer display", () => {
      const timerElement = document.getElementById("mcq-timer");

      assessmentModule.timeRemaining = 125; // 2:05
      assessmentModule.updateTimerDisplay();

      expect(timerElement.textContent).toBe("2:05");
    });

    test("should add warning class when time is low", () => {
      const timerElement = document.getElementById("mcq-timer");

      assessmentModule.timeRemaining = 250; // Less than 5 minutes
      assessmentModule.updateTimerDisplay();

      expect(timerElement.classList.contains("time-warning")).toBe(true);
    });

    test("should auto-submit when time runs out", () => {
      const submitSpy = jest.spyOn(assessmentModule, "submitAssessment");

      assessmentModule.timeRemaining = 1;
      assessmentModule.startTimer();

      // Fast-forward past the remaining time
      jest.advanceTimersByTime(2000);

      expect(submitSpy).toHaveBeenCalled();
    });
  });

  describe("Auto-save Functionality", () => {
    beforeEach(() => {
      assessmentModule.init();
      global.fetch.mockClear();
    });

    test("should auto-save progress when answer is selected", () => {
      assessmentModule.saveAnswer("B");

      expect(global.fetch).toHaveBeenCalledWith(
        global.mcqhome_assessment.ajax_url,
        expect.objectContaining({
          method: "POST",
        })
      );
    });

    test("should include correct data in auto-save", () => {
      assessmentModule.currentQuestion = 1;
      assessmentModule.answers = { 0: "B", 1: "C" };
      assessmentModule.autoSave();

      const callArgs = global.fetch.mock.calls[0];
      const formData = callArgs[1].body;

      // Check that the form data contains expected fields
      expect(formData.toString()).toContain("action=mcqhome_save_progress");
      expect(formData.toString()).toContain("current_question=1");
    });
  });

  describe("Assessment Submission", () => {
    beforeEach(() => {
      assessmentModule.init();
      global.fetch.mockClear();

      // Mock window.confirm to always return true
      global.confirm = jest.fn(() => true);
    });

    test("should submit assessment with answers", () => {
      assessmentModule.answers = { 0: "B", 1: "C" };
      assessmentModule.timeRemaining = 1200; // 20 minutes remaining

      assessmentModule.submitAssessment();

      expect(global.fetch).toHaveBeenCalledWith(
        global.mcqhome_assessment.ajax_url,
        expect.objectContaining({
          method: "POST",
        })
      );
    });

    test("should calculate time taken correctly", () => {
      assessmentModule.timeRemaining = 1200; // 20 minutes remaining out of 30
      assessmentModule.submitAssessment();

      const callArgs = global.fetch.mock.calls[0];
      const formData = callArgs[1].body;

      // Time taken should be 30 minutes - 20 minutes = 10 minutes = 600 seconds
      expect(formData.toString()).toContain("time_taken=600");
    });

    test("should not submit if user cancels confirmation", () => {
      global.confirm = jest.fn(() => false);

      assessmentModule.submitAssessment();

      expect(global.fetch).not.toHaveBeenCalled();
    });
  });

  describe("Question Display", () => {
    beforeEach(() => {
      assessmentModule.init();
    });

    test("should update question content", () => {
      assessmentModule.updateQuestion();

      const questionContainer = document.getElementById(
        "mcq-question-container"
      );
      expect(questionContainer.innerHTML).toContain("What is 2 + 2?");
      expect(questionContainer.innerHTML).toContain('value="A"');
      expect(questionContainer.innerHTML).toContain('value="B"');
      expect(questionContainer.innerHTML).toContain('value="C"');
      expect(questionContainer.innerHTML).toContain('value="D"');
    });

    test("should show previously selected answer", () => {
      assessmentModule.answers[0] = "B";
      assessmentModule.updateQuestion();

      const questionContainer = document.getElementById(
        "mcq-question-container"
      );
      expect(questionContainer.innerHTML).toContain("checked");
    });

    test("should update navigation buttons based on current question", () => {
      const prevBtn = document.getElementById("mcq-prev-btn");
      const nextBtn = document.getElementById("mcq-next-btn");
      const submitBtn = document.getElementById("mcq-submit-btn");

      // First question
      assessmentModule.currentQuestion = 0;
      assessmentModule.updateNavigation();

      expect(prevBtn.disabled).toBe(true);
      expect(nextBtn.style.display).toBe("inline-block");
      expect(submitBtn.style.display).toBe("none");

      // Last question
      assessmentModule.currentQuestion = 1;
      assessmentModule.updateNavigation();

      expect(prevBtn.disabled).toBe(false);
      expect(nextBtn.style.display).toBe("none");
      expect(submitBtn.style.display).toBe("inline-block");
    });
  });

  describe("Event Handling", () => {
    beforeEach(() => {
      assessmentModule.init();
    });

    test("should handle radio button changes", () => {
      const radioButton = document.querySelector(
        'input[type="radio"][value="B"]'
      );
      const saveAnswerSpy = jest.spyOn(assessmentModule, "saveAnswer");

      radioButton.checked = true;
      radioButton.dispatchEvent(new Event("change", { bubbles: true }));

      expect(saveAnswerSpy).toHaveBeenCalledWith("B");
    });

    test("should handle question navigation button clicks", () => {
      const goToQuestionSpy = jest.spyOn(assessmentModule, "goToQuestion");
      const navBtn = document.querySelector(
        '.question-nav-btn[data-question="1"]'
      );

      navBtn.click();

      expect(goToQuestionSpy).toHaveBeenCalledWith(1);
    });
  });
});
