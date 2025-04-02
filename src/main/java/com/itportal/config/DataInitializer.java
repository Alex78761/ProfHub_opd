package com.itportal.config;

import com.itportal.model.User;
import com.itportal.model.UserRole;
import com.itportal.service.UserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.CommandLineRunner;
import org.springframework.stereotype.Component;

@Component
public class DataInitializer implements CommandLineRunner {

    private final UserService userService;

    @Autowired
    public DataInitializer(UserService userService) {
        this.userService = userService;
    }

    @Override
    public void run(String... args) {
        // Создаем администратора, если его нет в базе
        if (!userService.existsByUsername("admin")) {
            User admin = new User();
            admin.setUsername("admin");
            admin.setEmail("admin@profhub.ru");
            admin.setPassword("admin123");
            userService.registerAdmin(admin);
            System.out.println("Администратор успешно создан");
        }

        // Создаем консультанта, если его нет в базе
        if (!userService.existsByUsername("consultant")) {
            User consultant = new User();
            consultant.setUsername("consultant");
            consultant.setEmail("consultant@profhub.ru");
            consultant.setPassword("consultant123");
            consultant.setFirstName("Иван");
            consultant.setLastName("Консультантов");
            userService.registerConsultant(consultant);
            System.out.println("Консультант успешно создан");
        }
    }
} 