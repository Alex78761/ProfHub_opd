package com.itportal.controller;

import com.itportal.model.Profession;
import com.itportal.service.ProfessionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;

@Controller
@RequestMapping("/professions")
public class ProfessionController {

    private final ProfessionService professionService;

    @Autowired
    public ProfessionController(ProfessionService professionService) {
        this.professionService = professionService;
    }

    @GetMapping
    public String listProfessions(Model model) {
        model.addAttribute("professions", professionService.getAllProfessions());
        return "professions/list";
    }

    @GetMapping("/{id}")
    public String showProfession(@PathVariable Long id, Model model) {
        model.addAttribute("profession", professionService.getProfessionById(id));
        return "professions/show";
    }
} 