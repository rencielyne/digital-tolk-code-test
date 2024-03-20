## Thoughts about the code

- My thoughts about the code is, it was very hard to refactor because even if it's already using a framework, it still like a procedural way. Almost all the methods in the BookingRepository has a complex logic, so many if-else statements to think that the purpose of repository class is for database related queries.

- The BookingRepository is like a general repository for all booking related stuff (even if almost all Model is about Job) thats why all the logic about bookings are there that's why the SRP or Single Responsibility Principle is also not implemented. 

- So I created a Service classes to handle and separate the logic and the actual database queries. I created Service class on every actions that the booking can make and to also implement the SRP, to also make the code mode maintainable and easy to trace.

- I also created interfaces for each Service class to implement the Dependency Inversion Principle to make the code more maintainable and testable.

- I also utilized the Laravel framework by using the Validator facade to validate the request payload instead of writing many if-else statement to ask if the payload is valid or not. And also created a custom exception for proper error handling and to easy trace the bug or where the errors occurred.

- Though, I can say that the BaseRepository is extendable which I also used its methods when I created another repositories. Also, the BookingController is kinda neat and clean, with minimal if-else statements it just that, it also has all methods related to the booking stuff, so I created another controller to at least properly segregate their methods.

- As much as I want to really refactor all the codes, specially the complex logic, like so many if-else statements, ifs inside ifs... the time is limited so I refactored only some methods.


## Thoughts about the testing

- I created a unit test on method willExpireAt based on the instruction. I noticed that the test will always fail because the code has a buggy logic that the condition will always fall on either test_will_expire_at_less_than_90_hours or test_will_expire_at_greater_than_90_hour.

- I decided to create only a test and not fix the willExpireAt method since the only instruction is to write a test.