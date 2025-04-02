package com.itportal.controller;

import com.itportal.model.User;
import com.itportal.service.UserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

@Controller
public class AuthController {

    private final UserService userService;

    @Autowired
    public AuthController(UserService userService) {
        this.userService = userService;
    }

    @GetMapping("/register")
    public String showRegistrationForm(Model model) {
        if (!model.containsAttribute("user")) {
            model.addAttribute("user", new User());
        }
        return "auth/register";
    }

    @PostMapping("/register")
    public String registerUser(@ModelAttribute("user") User user, RedirectAttributes redirectAttributes) {
        // Проверяем, существует ли пользователь с таким email или username
        if (userService.existsByEmail(user.getEmail())) {
            redirectAttributes.addFlashAttribute("error", "Пользователь с таким email уже существует");
            redirectAttributes.addFlashAttribute("user", user);
            return "redirect:/register";
        }

        if (userService.existsByUsername(user.getUsername())) {
            redirectAttributes.addFlashAttribute("error", "Пользователь с таким именем уже существует");
            redirectAttributes.addFlashAttribute("user", user);
            return "redirect:/register";
        }

        try {
            userService.registerUser(user);
            redirectAttributes.addFlashAttribute("success", "Регистрация успешно завершена");
            return "redirect:/login";
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Произошла ошибка при регистрации");
            redirectAttributes.addFlashAttribute("user", user);
            return "redirect:/register";
        }
    }

    @GetMapping("/login")
    public String showLoginForm() {
        return "auth/login";
    }
} 