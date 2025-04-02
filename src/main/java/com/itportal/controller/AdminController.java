package com.itportal.controller;

import com.itportal.model.User;
import com.itportal.service.UserService;
import com.itportal.model.Profession;
import com.itportal.service.ProfessionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

@Controller
@RequestMapping("/admin")
@PreAuthorize("hasRole('ADMIN')")
public class AdminController {

    private final UserService userService;
    private final ProfessionService professionService;

    @Autowired
    public AdminController(UserService userService, ProfessionService professionService) {
        this.userService = userService;
        this.professionService = professionService;
    }

    @GetMapping("/experts/new")
    public String showExpertRegistrationForm(Model model) {
        model.addAttribute("user", new User());
        return "admin/register-expert";
    }

    @PostMapping("/experts/new")
    public String registerExpert(@ModelAttribute User user) {
        userService.registerExpert(user);
        return "redirect:/admin/experts";
    }

    @GetMapping("/consultants/new")
    public String showConsultantRegistrationForm(Model model) {
        model.addAttribute("user", new User());
        return "admin/register-consultant";
    }

    @PostMapping("/consultants/new")
    public String registerConsultant(@ModelAttribute User user) {
        userService.registerConsultant(user);
        return "redirect:/admin/consultants";
    }

    @GetMapping("/professions/add")
    public String showAddProfessionForm() {
        return "admin/add-profession";
    }

    @PostMapping("/professions/add")
    public String addProfession(@RequestParam String name,
                              @RequestParam String description,
                              @RequestParam String requirements,
                              @RequestParam Integer salary,
                              RedirectAttributes redirectAttributes) {
        try {
            Authentication auth = SecurityContextHolder.getContext().getAuthentication();
            User currentUser = userService.findByUsername(auth.getName());

            Profession profession = new Profession();
            profession.setName(name);
            profession.setDescription(description);
            profession.setRequirements(requirements);
            profession.setSalary(salary);
            profession.setCreatedBy(currentUser);
            
            professionService.saveProfession(profession);
            redirectAttributes.addFlashAttribute("success", "Профессия успешно добавлена");
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Ошибка при добавлении профессии: " + e.getMessage());
        }
        return "redirect:/admin/professions";
    }

    @GetMapping("/professions")
    public String listProfessions(Model model) {
        model.addAttribute("professions", professionService.getAllProfessions());
        return "admin/professions";
    }

    @GetMapping
    public String adminDashboard(Model model) {
        model.addAttribute("users", userService.getAllUsers());
        model.addAttribute("professions", professionService.getAllProfessions());
        return "admin/dashboard";
    }
} 